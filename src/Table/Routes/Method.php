<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Table\Routes;

use PSX\Api\Resource;
use PSX\Sql\TableAbstract;

/**
 * Method
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Method extends TableAbstract
{
    public function getName()
    {
        return 'fusio_routes_method';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'routeId' => self::TYPE_INT,
            'method' => self::TYPE_VARCHAR,
            'version' => self::TYPE_INT,
            'status' => self::TYPE_INT,
            'active' => self::TYPE_INT,
            'public' => self::TYPE_INT,
            'parameters' => self::TYPE_INT,
            'request' => self::TYPE_INT,
            'action' => self::TYPE_INT,
            'schemaCache' => self::TYPE_TEXT,
            'actionCache' => self::TYPE_TEXT,
        );
    }

    public function deleteAllFromRoute($routeId, $version = null, $status = null)
    {
        $sql = 'DELETE FROM fusio_routes_method
                      WHERE routeId = :id';

        $params = ['id' => $routeId];

        if ($version !== null) {
            $sql.= ' AND version = :version';
            $params['version'] = $version;
        }

        if ($status !== null) {
            $sql.= ' AND status = :status';
            $params['status'] = $status;
        }

        $this->connection->executeQuery($sql, $params);
    }

    /**
     * Returns whether a schema id is in use by a route. In the worst case this
     * gets reported by the database constraints but we use this method to 
     * report a proper error message
     * 
     * @param integer $schemaId
     * @return boolean
     */
    public function hasSchema($schemaId)
    {
        $sql = '    SELECT COUNT(resp.id) AS cnt
                      FROM fusio_routes_response resp
                INNER JOIN fusio_routes_method method
                        ON resp.methodId = method.id
                INNER JOIN fusio_routes routes
                        ON routes.id = method.routeId
                     WHERE routes.status = 1
                       AND (method.parameters = :schemaId OR method.request = :schemaId OR resp.response = :schemaId)';

        $count = $this->connection->fetchColumn($sql, [
            'schemaId' => $schemaId,
        ]);

        return $count > 0;
    }

    /**
     * Returns whether a action id is in use by a route. In the worst case this
     * gets reported by the database constraints but we use this method to
     * report a proper error message
     *
     * @param integer $actionId
     * @return boolean
     */
    public function hasAction($actionId)
    {
        $sql = '    SELECT COUNT(method.id) AS cnt
                      FROM fusio_routes_method method
                INNER JOIN fusio_routes routes
                        ON routes.id = method.routeId
                     WHERE routes.status = 1
                       AND method.action = :actionId';

        $count = $this->connection->fetchColumn($sql, [
            'actionId' => $actionId,
        ]);

        return $count > 0;
    }

    /**
     * Returns only active methods for the route
     *
     * @param integer $routeId
     * @param integer $version
     * @param boolean $active
     * @param boolean $cache
     * @return array
     */
    public function getMethods($routeId, $version = null, $active = true, $cache = false)
    {
        $fields = ['method.id', 'method.routeId', 'method.version', 'method.status', 'method.method', 'method.active', 'method.public', 'method.parameters', 'method.request', 'method.action'];
        if ($cache) {
            $fields[] = 'method.schemaCache';
        }

        $sql = '  SELECT ' . implode(',', $fields) . '
                    FROM fusio_routes_method method
                   WHERE method.routeId = :routeId';

        $params = ['routeId' => $routeId];

        if ($active !== null) {
            $sql.= ' AND method.active = ' . ($active ? '1' : '0');
        }

        if ($version !== null) {
            $sql.= ' AND method.version = :version';
            $params['version'] = $version;
        }

        $sql.= ' ORDER BY method.version ASC';

        return $this->project($sql, $params);
    }

    public function getAllowedMethods($routeId, $version)
    {
        $methods = $this->getMethods($routeId, $version);
        $names   = [];

        foreach ($methods as $method) {
            $names[] = $method['method'];
        }

        return $names;
    }

    public function getMethod($routeId, $version, $method)
    {
        $sql = 'SELECT method.public,
                       method.action,
                       method.status,
                       method.actionCache
                  FROM fusio_routes_method method
                 WHERE routeId = :routeId
                   AND version = :version
                   AND method = :method
                   AND active = :active';

        return $this->connection->fetchAssoc($sql, [
            'routeId' => $routeId,
            'version' => $version,
            'method'  => $method,
            'active'  => Resource::STATUS_ACTIVE,
        ]);
    }

    public function getVersion($routeId, $version)
    {
        $sql = 'SELECT version
                  FROM fusio_routes_method
                 WHERE routeId = :routeId
                   AND version = :version';

        return $this->connection->fetchColumn($sql, [
            'routeId' => $routeId,
            'version' => $version,
        ]);
    }

    public function getLatestVersion($routeId)
    {
        $sql = 'SELECT MAX(version)
                  FROM fusio_routes_method
                 WHERE routeId = :routeId
                   AND status = :status';

        $version = $this->connection->fetchColumn($sql, [
            'routeId' => $routeId,
            'status'  => Resource::STATUS_ACTIVE,
        ]);

        if (empty($version)) {
            // in case we have no production version we try to select any max
            // version
            $sql = 'SELECT MAX(version)
                      FROM fusio_routes_method
                     WHERE routeId = :routeId';

            return $this->connection->fetchColumn($sql, [
                'routeId' => $routeId,
            ]);
        } else {
            return $version;
        }
    }

    public function hasProductionVersion($routeId)
    {
        $sql = 'SELECT COUNT(id)
                  FROM fusio_routes_method
                 WHERE routeId = :id
                   AND status IN (:production, :deprecated)
                   AND active = :active';

        $count = (int) $this->connection->fetchColumn($sql, [
            'id'         => $routeId,
            'production' => Resource::STATUS_ACTIVE,
            'deprecated' => Resource::STATUS_DEPRECATED,
            'active'     => 1,
        ]);

        return $count > 1;
    }
}
