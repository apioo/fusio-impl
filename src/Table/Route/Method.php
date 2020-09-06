<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Table\Route;

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
            'route_id' => self::TYPE_INT,
            'method' => self::TYPE_VARCHAR,
            'version' => self::TYPE_INT,
            'status' => self::TYPE_INT,
            'active' => self::TYPE_INT,
            'public' => self::TYPE_INT,
            'description' => self::TYPE_VARCHAR,
            'operation_id' => self::TYPE_VARCHAR,
            'parameters' => self::TYPE_VARCHAR,
            'request' => self::TYPE_VARCHAR,
            'action' => self::TYPE_INT,
            'costs' => self::TYPE_INT,
        );
    }

    public function deleteAllFromRoute($routeId, $version = null, $status = null)
    {
        $sql = 'DELETE FROM fusio_routes_method
                      WHERE route_id = :id';

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
        $fields = ['method.id', 'method.route_id', 'method.version', 'method.status', 'method.method', 'method.active', 'method.public', 'method.description', 'method.operation_id', 'method.parameters', 'method.request', 'method.action', 'method.costs'];
        if ($cache) {
            $fields[] = 'method.schema_cache';
        }

        $sql = '  SELECT ' . implode(',', $fields) . '
                    FROM fusio_routes_method method
                   WHERE method.route_id = :route_id';

        $params = ['route_id' => $routeId];

        if ($active !== null) {
            $sql.= ' AND method.active = ' . ($active ? '1' : '0');
        }

        if ($version !== null) {
            $sql.= ' AND method.version = :version';
            $params['version'] = $version;
        }

        $sql.= ' ORDER BY method.version ASC, method.id ASC';

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
                       method.operation_id,
                       method.action,
                       method.status,
                       method.costs,
                       method.action_cache
                  FROM fusio_routes_method method
                 WHERE route_id = :route_id
                   AND version = :version
                   AND method = :method
                   AND active = :active';

        return $this->connection->fetchAssoc($sql, [
            'route_id' => $routeId,
            'version' => $version,
            'method' => $method,
            'active' => Resource::STATUS_ACTIVE,
        ]);
    }

    public function getMethodByOperationId($operationId)
    {
        $sql = 'SELECT method.route_id,
                       method.method,
                       method.public,
                       method.operation_id,
                       method.parameters,
                       method.request,
                       method.action,
                       method.status,
                       method.costs,
                       method.action_cache,
                       method.schema_cache
                  FROM fusio_routes_method method
                 WHERE method.operation_id = :operation_id
                   AND method.active = :active';

        return $this->connection->fetchAssoc($sql, [
            'operation_id' => $operationId,
            'active' => Resource::STATUS_ACTIVE,
        ]);
    }

    public function getVersion($routeId, $version)
    {
        $sql = 'SELECT version
                  FROM fusio_routes_method
                 WHERE route_id = :route_id
                   AND version = :version';

        return $this->connection->fetchColumn($sql, [
            'route_id' => $routeId,
            'version' => $version,
        ]);
    }

    public function getLatestVersion($routeId)
    {
        $sql = 'SELECT MAX(version)
                  FROM fusio_routes_method
                 WHERE route_id = :route_id
                   AND status = :status';

        $version = $this->connection->fetchColumn($sql, [
            'route_id' => $routeId,
            'status' => Resource::STATUS_ACTIVE,
        ]);

        if (empty($version)) {
            // in case we have no production version we try to select any max
            // version
            $sql = 'SELECT MAX(version)
                      FROM fusio_routes_method
                     WHERE route_id = :route_id';

            return $this->connection->fetchColumn($sql, [
                'route_id' => $routeId,
            ]);
        } else {
            return $version;
        }
    }

    public function hasProductionVersion($routeId)
    {
        $sql = 'SELECT COUNT(id) AS cnt
                  FROM fusio_routes_method
                 WHERE route_id = :route_id
                   AND status IN (:production, :deprecated)
                   AND active = :active';

        $count = (int) $this->connection->fetchColumn($sql, [
            'route_id'   => $routeId,
            'production' => Resource::STATUS_ACTIVE,
            'deprecated' => Resource::STATUS_DEPRECATED,
            'active'     => 1,
        ]);

        return $count > 0;
    }
}
