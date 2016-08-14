<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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
use PSX\Sql\Condition;
use PSX\Sql\TableAbstract;

/**
 * Method
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
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
            'request' => self::TYPE_INT,
            'requestCache' => self::TYPE_TEXT,
            'response' => self::TYPE_INT,
            'responseCache' => self::TYPE_TEXT,
            'action' => self::TYPE_INT,
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

    public function hasSchema($schemaId)
    {
        $sql = '    SELECT COUNT(method.id) AS cnt
                      FROM fusio_routes_method method
                INNER JOIN fusio_routes routes
                        ON routes.id = method.routeId
                     WHERE routes.status = 1
                       AND (method.request = :requestId 
                           OR method.response = :responseId)';

        $count = $this->connection->fetchColumn($sql, [
            'requestId'  => $schemaId,
            'responseId' => $schemaId,
        ]);

        return $count > 0;
    }

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
     * @param boolean $includeCache
     * @param boolean $active
     * @return array
     */
    public function getMethods($routeId, $version = null, $includeCache = false, $active = true)
    {
        $cache = '';
        if ($includeCache) {
            $cache.= 'method.requestCache,method.responseCache,method.actionCache,';
        }

        $sql = '  SELECT method.id,
                         method.routeId,
                         method.version,
                         method.status,
                         method.method,
                         method.active,
                         method.public,
                         ' . $cache . '
                         method.request,
                         method.response,
                         method.action
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

    public function getLatestVersion($routeId)
    {
        $sql = 'SELECT MAX(version)
                  FROM fusio_routes_method
                 WHERE routeId = :id
                   AND status = :status';

        $version = $this->connection->fetchColumn($sql, [
            'id'     => $routeId,
            'status' => Resource::STATUS_ACTIVE,
        ]);

        if (empty($version)) {
            // in case we have no production version we try to select any max
            // version
            $sql = 'SELECT MAX(version)
                      FROM fusio_routes_method
                     WHERE routeId = :id';

            return $this->connection->fetchColumn($sql, [
                'id' => $routeId,
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
