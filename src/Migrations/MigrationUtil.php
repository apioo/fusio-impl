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

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Connection;

/**
 * MigrationUtil
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class MigrationUtil
{
    public static function sync(Connection $connection, \Closure $callback): void
    {
        $data = NewInstallation::getData();

        self::syncConfig($connection, $data['fusio_config'], $callback);
        self::syncRoutes($connection, $data['fusio_routes'], $callback);
    }

    /**
     * Helper method to sync all config values of an existing system
     */
    public static function syncConfig(Connection $connection, array $configs, \Closure $callback): void
    {
        foreach ($configs as $row) {
            $config = $connection->fetchAssoc('SELECT id, name FROM fusio_config WHERE name = :name', [
                'name' => $row['name']
            ]);

            if (empty($config)) {
                self::insertRow('fusio_config', $row, $callback);
            }
        }
    }

    /**
     * Helper method to sync all routes of an existing system
     */
    public static function syncRoutes(Connection $connection, array $routes, \Closure $callback): void
    {
        foreach ($routes as $row) {
            $route = $connection->fetchAssoc('SELECT id, category_id, status, priority, methods, controller FROM fusio_routes WHERE path = :path', [
                'path' => $row['path']
            ]);

            if (empty($route)) {
                // the route does not exist so create it
                self::insertRow('fusio_routes', $row, $callback);
            } else {
                // the route exists check whether something has changed
                $columns = ['status', 'priority', 'controller'];

                self::updateRow('fusio_routes', $row, $route, $columns, $callback);
            }
        }
    }

    public static function insertRow(string $tableName, array $row, \Closure $callback): void
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

    public static function updateRow(string $tableName, array $row, array $existing, array $columns, \Closure $callback): void
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
