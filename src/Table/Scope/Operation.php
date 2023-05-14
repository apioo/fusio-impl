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
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Operation extends Generated\ScopeOperationTable
{
    public function deleteAllFromScope(int $scopeId): void
    {
        $sql = 'DELETE FROM fusio_scope_operation
                      WHERE scope_id = :id';

        $this->connection->executeQuery($sql, array('id' => $scopeId));
    }

    public function deleteAllFromOperation(int $operationId): void
    {
        $sql = 'DELETE FROM fusio_scope_operation
                      WHERE route_id = :id';

        $this->connection->executeQuery($sql, array('id' => $operationId));
    }

    public function getScopeNamesForOperation(int $operationId): array
    {
        $sql = 'SELECT scope.name
                  FROM fusio_scope_operation operation
            INNER JOIN fusio_scope scope
                    ON scope.id = operation.scope_id
                 WHERE operation.route_id = :id
                   AND operation.allow = 1
              ORDER BY operation.id ASC';

        return $this->connection->fetchAllAssociative($sql, ['id' => $operationId]);
    }

    public function getScopesForOperation(int $operationId): array
    {
        $sql = 'SELECT scope.name,
                       operation.methods
                  FROM fusio_scope_operation operation
            INNER JOIN fusio_scope scope
                    ON scope.id = operation.scope_id
                 WHERE operation.route_id = :id
                   AND operation.allow = 1
              ORDER BY operation.id ASC';

        $result = $this->connection->fetchAllAssociative($sql, ['id' => $operationId]);
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
