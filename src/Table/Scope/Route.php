<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table\Generated;

/**
 * Route
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Route extends Generated\ScopeRoutesTable
{
    public function deleteAllFromScope($scopeId)
    {
        $sql = 'DELETE FROM fusio_scope_routes
                      WHERE scope_id = :id';

        $this->connection->executeQuery($sql, array('id' => $scopeId));
    }

    public function deleteAllFromRoute($routeId)
    {
        $sql = 'DELETE FROM fusio_scope_routes
                      WHERE route_id = :id';

        $this->connection->executeQuery($sql, array('id' => $routeId));
    }

    public function getScopeNamesForRoute($routeId)
    {
        $sql = 'SELECT scope.name
                  FROM fusio_scope_routes routes
            INNER JOIN fusio_scope scope
                    ON scope.id = routes.scope_id
                 WHERE routes.route_id = :id
                   AND routes.allow = 1
              ORDER BY routes.id ASC';

        return $this->connection->fetchAll($sql, ['id' => $routeId]);
    }

    public function getScopesForRoute($routeId)
    {
        $sql = 'SELECT scope.name,
                       routes.methods
                  FROM fusio_scope_routes routes
            INNER JOIN fusio_scope scope
                    ON scope.id = routes.scope_id
                 WHERE routes.route_id = :id
                   AND routes.allow = 1
              ORDER BY routes.id ASC';

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
