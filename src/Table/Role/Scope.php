<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Table\Role;

use Fusio\Impl\Table\Generated;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\RoleScopeTable
{
    public function deleteAllFromRole($roleId)
    {
        $sql = 'DELETE FROM fusio_role_scope
                      WHERE role_id = :id';

        $this->connection->executeQuery($sql, ['id' => $roleId]);
    }

    public function getValidScopes($roleId, array $scopes)
    {
        $result = $this->getAvailableScopes($roleId);
        $data = [];

        foreach ($result as $scope) {
            if (in_array($scope['name'], $scopes)) {
                $data[] = $scope;
            }
        }

        return $data;
    }

    public function getAvailableScopes($roleId)
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_role_scope role_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = role_scope.scope_id
                     WHERE role_scope.role_id = :role_id
                  ORDER BY scope.id ASC';
        $assignedScopes = $this->connection->fetchAllAssociative($sql, ['role_id' => $roleId]) ?: [];

        $scopes = [];
        foreach ($assignedScopes as $assignedScope) {
            $scopes[$assignedScope['name']] = $assignedScope;

            if (!str_contains($assignedScope['name'], '.')) {
                // load all sub scopes
                $sql = 'SELECT scope.id,
                               scope.name,
                               scope.description
                          FROM fusio_scope scope
                         WHERE scope.name LIKE :name
                      ORDER BY scope.name ASC';
                $subScopes = $this->connection->fetchAllAssociative($sql, ['name' => $assignedScope['name'] . '.%']);
                foreach ($subScopes as $subScope) {
                    $scopes[$subScope['name']] = $subScope;
                }
            }
        }

        return array_values($scopes);
    }
}
