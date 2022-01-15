<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Table\Generated;

/**
 * Method
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Method extends Generated\RoutesMethodTable
{
    public function deleteAllFromRoute(int $routeId, ?int $version = null, ?int $status = null): void
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
     */
    public function getMethods(int $routeId, ?int $version = null, ?bool $active = true): array
    {
        $sql = '  SELECT method.id,
                         method.route_id,
                         method.version,
                         method.status,
                         method.method,
                         method.active,
                         method.public,
                         method.description,
                         method.operation_id,
                         method.parameters,
                         method.request,
                         method.action,
                         method.costs
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

    public function getAllowedMethods(int $routeId, ?string $version): array
    {
        $methods = $this->getMethods($routeId, $version);
        $names   = [];

        foreach ($methods as $method) {
            $names[] = $method['method'];
        }

        return $names;
    }

    public function getMethod(int $routeId, int $version, string $method): array|false
    {
        $sql = 'SELECT method.route_id,
                       method.public,
                       method.operation_id,
                       method.action,
                       method.status,
                       method.costs
                  FROM fusio_routes_method method
                 WHERE route_id = :route_id
                   AND version = :version
                   AND method = :method
                   AND active = :active';

        return $this->connection->fetchAssociative($sql, [
            'route_id' => $routeId,
            'version' => $version,
            'method' => $method,
            'active' => Resource::STATUS_ACTIVE,
        ]);
    }

    public function getMethodByOperationId(string $operationId): array|false
    {
        $sql = 'SELECT method.route_id,
                       method.method,
                       method.status,
                       method.public,
                       method.operation_id,
                       method.parameters,
                       method.request,
                       method.action,
                       method.costs
                  FROM fusio_routes_method method
                 WHERE method.operation_id = :operation_id
                   AND method.active = :active';

        return $this->connection->fetchAssociative($sql, [
            'operation_id' => $operationId,
            'active' => Resource::STATUS_ACTIVE,
        ]);
    }

    public function getVersion(int $routeId, ?string $version): string|false
    {
        $sql = 'SELECT version
                  FROM fusio_routes_method
                 WHERE route_id = :route_id
                   AND version = :version';

        return $this->connection->fetchOne($sql, [
            'route_id' => $routeId,
            'version' => $version,
        ]);
    }

    public function getLatestVersion(int $routeId)
    {
        $sql = 'SELECT MAX(version)
                  FROM fusio_routes_method
                 WHERE route_id = :route_id
                   AND status = :status';

        $version = $this->connection->fetchOne($sql, [
            'route_id' => $routeId,
            'status' => Resource::STATUS_ACTIVE,
        ]);

        if (empty($version)) {
            // in case we have no production version we try to select any max
            // version
            $sql = 'SELECT MAX(version)
                      FROM fusio_routes_method
                     WHERE route_id = :route_id';

            return $this->connection->fetchOne($sql, [
                'route_id' => $routeId,
            ]);
        } else {
            return $version;
        }
    }

    public function hasProductionVersion(int $routeId): bool
    {
        $sql = 'SELECT COUNT(id) AS cnt
                  FROM fusio_routes_method
                 WHERE route_id = :route_id
                   AND status IN (:production, :deprecated)
                   AND active = :active';

        $count = (int) $this->connection->fetchOne($sql, [
            'route_id'   => $routeId,
            'production' => Resource::STATUS_ACTIVE,
            'deprecated' => Resource::STATUS_DEPRECATED,
            'active'     => 1,
        ]);

        return $count > 0;
    }
}
