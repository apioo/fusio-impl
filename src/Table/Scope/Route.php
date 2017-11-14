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

namespace Fusio\Impl\Table\Scope;

use PSX\Sql\TableAbstract;

/**
 * Route
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Route extends TableAbstract
{
    public function getName()
    {
        return 'fusio_scope_routes';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'scopeId' => self::TYPE_INT,
            'routeId' => self::TYPE_INT,
            'allow' => self::TYPE_INT,
            'methods' => self::TYPE_VARCHAR,
        );
    }

    public function deleteAllFromScope($scopeId)
    {
        $sql = 'DELETE FROM fusio_scope_routes
                      WHERE scopeId = :id';

        $this->connection->executeQuery($sql, array('id' => $scopeId));
    }

    public function deleteAllFromRoute($routeId)
    {
        $sql = 'DELETE FROM fusio_scope_routes
                      WHERE routeId = :id';

        $this->connection->executeQuery($sql, array('id' => $routeId));
    }

    public function getScopesForRoute($routeId)
    {
        $sql = 'SELECT scope.name,
                       routes.methods
                  FROM fusio_scope_routes routes
            INNER JOIN fusio_scope scope
                    ON scope.id = routes.scopeId
                 WHERE routeId = :id
                   AND allow = 1';

        $result = $this->connection->fetchAll($sql, ['id' => $routeId]);
        $scopes = [];

        foreach ($result as $row) {
            $methods = explode('|', $row['methods']);
            foreach ($methods as $methodName) {
                if (!isset($scopes[$methodName])) {
                    $scopes[$methodName] = [];
                }

                $scopes[$methodName][] = $row['name'];
            }
        }

        return $scopes;
    }
}
