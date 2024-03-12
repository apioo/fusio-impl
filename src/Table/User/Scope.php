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

namespace Fusio\Impl\Table\User;

use Fusio\Impl\Table\Generated;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\UserScopeTable
{
    public function deleteAllFromUser(int $userId): void
    {
        $sql = 'DELETE FROM fusio_user_scope
                      WHERE user_id = :id';

        $this->connection->executeStatement($sql, ['id' => $userId]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getValidScopes(?string $tenantId, int $userId, array $scopes): array
    {
        $result = $this->getAvailableScopes($tenantId, $userId, true);
        $data   = array();

        foreach ($result as $scope) {
            if (in_array($scope['name'], $scopes)) {
                $data[] = $scope;
            }
        }

        return $data;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAvailableScopes(?string $tenantId, int $userId, bool $includePlanScopes = false): array
    {
        $assignedScopes = $this->getScopesForUser($tenantId, $userId);

        // get scopes for plan
        if ($includePlanScopes) {
            $assignedScopes = array_merge($assignedScopes, $this->getScopesForPlan($tenantId, $userId));
        }

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

    private function getScopesForUser(?string $tenantId, int $userId): array
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_user_scope user_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = user_scope.scope_id
                     WHERE user_scope.user_id = :user_id
                  ORDER BY scope.id ASC';
        return $this->connection->fetchAllAssociative($sql, ['user_id' => $userId]) ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getScopesForPlan(?string $tenantId, int $userId): array
    {
        $planId = (int) $this->connection->fetchOne('SELECT plan_id FROM fusio_user WHERE id = :user_id', ['user_id' => $userId]);
        if (empty($planId)) {
            return [];
        }

        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_plan_scope plan_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = plan_scope.scope_id
                     WHERE plan_scope.plan_id = :plan_id
                  ORDER BY scope.id ASC';
        return $this->connection->fetchAllAssociative($sql, ['plan_id' => $planId]) ?: [];
    }
}
