<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Connection;

/**
 * MigrationUtil
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class MigrationUtil
{
    /**
     * Helper method to sync all routes of an existing system with the routes
     * defined in the new installation class
     * 
     * @param \Doctrine\DBAL\Connection $connection
     * @param array $routes
     * @param \Closure $callback
     */
    public static function syncRoutes(Connection $connection, array $routes, \Closure $callback)
    {
        $scopes = [];
        $maxId  = (int) $connection->fetchColumn('SELECT MAX(id) AS max_id FROM fusio_routes');

        foreach ($routes as $row) {
            $route = $connection->fetchAssoc('SELECT id, status, priority, methods, controller FROM fusio_routes WHERE path = :path', [
                'path' => $row['path']
            ]);

            if (empty($route)) {
                // the route does not exist so create it
                self::insertRow('fusio_routes', $row, $callback);

                $maxId++;

                $scopeId = NewInstallation::getScopeIdFromPath($row['path']);
                if ($scopeId !== null) {
                    $scopes[] = [
                        'scope_id' => $scopeId,
                        'route_id' => $maxId,
                        'allow'    => 1,
                        'methods'  => 'GET|POST|PUT|PATCH|DELETE',
                    ];
                }
            } else {
                // the route exists check whether something has changed
                $columns = ['status', 'priority', 'controller'];

                self::updateRow('fusio_routes', $row, $route, $columns, $callback);
            }
        }

        if (!empty($scopes)) {
            // add new routes to scopes
            foreach ($scopes as $row) {
                self::insertRow('fusio_scope_routes', $row, $callback);
            }
        }
    }

    /**
     * @param string $tableName
     * @param array $row
     * @param \Closure $callback
     */
    public static function insertRow($tableName, array $row, \Closure $callback)
    {
        $columnList = [];
        $paramPlaceholders = [];
        $paramValues = [];

        foreach ($row as $columnName => $value) {
            $columnList[] = $columnName;
            $paramPlaceholders[] = '?';
            $paramValues[] = $value;
        }

        if (!empty($columnList)) {
            $sql = 'INSERT INTO ' . $tableName . ' (' . implode(', ', $columnList) . ') VALUES (' . implode(', ', $paramPlaceholders) . ')';

            $callback($sql, $paramValues);
        }
    }

    /**
     * @param string $tableName
     * @param array $row
     * @param array $existing
     * @param array $columns
     * @param \Closure $callback
     */
    public static function updateRow($tableName, array $row, array $existing, array $columns, \Closure $callback)
    {
        $parts  = [];
        $params = [];
        foreach ($columns as $column) {
            if (!isset($row[$column])) {
                throw new \RuntimeException('Column does not exist on new row');
            }

            if (!isset($existing[$column])) {
                throw new \RuntimeException('Column does not exist on existing row');
            }

            if ($row[$column] != $existing[$column]) {
                $parts[] = $column . ' = :' . $column;
                $params[$column] = $row[$column];
            }
        }

        if (!empty($params)) {
            $sql = 'UPDATE ' . $tableName . ' SET ' . implode(', ', $parts). ' WHERE id = :id';
            $params['id'] = $existing['id'];

            $callback($sql, $params);
        }
    }
}
