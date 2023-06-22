<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Table\Scope;

use Fusio\Impl\Table\Generated;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Operation extends Generated\ScopeOperationTable
{
    public function deleteAllFromScope(int $scopeId): void
    {
        $sql = 'DELETE FROM fusio_scope_operation
                      WHERE scope_id = :id';

        $this->connection->executeQuery($sql, ['id' => $scopeId]);
    }

    public function deleteAllFromOperation(int $operationId): void
    {
        $sql = 'DELETE FROM fusio_scope_operation
                      WHERE operation_id = :id';

        $this->connection->executeQuery($sql, ['id' => $operationId]);
    }

    public function getScopeNamesForOperation(int $operationId): array
    {
        $sql = 'SELECT scope.name
                  FROM fusio_scope_operation operation
            INNER JOIN fusio_scope scope
                    ON scope.id = operation.scope_id
                 WHERE operation.operation_id = :id
                   AND operation.allow = 1
              ORDER BY operation.id ASC';

        return $this->connection->fetchAllAssociative($sql, ['id' => $operationId]);
    }

    public function getScopesForOperation(int $operationId): array
    {
        $sql = 'SELECT scope.name
                  FROM fusio_scope_operation operation
            INNER JOIN fusio_scope scope
                    ON scope.id = operation.scope_id
                 WHERE operation.operation_id = :id
                   AND operation.allow = 1
              ORDER BY operation.id ASC';
        return $this->connection->fetchFirstColumn($sql, ['id' => $operationId]);
    }
}
