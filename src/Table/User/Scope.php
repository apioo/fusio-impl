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

namespace Fusio\Impl\Table\User;

use Fusio\Impl\Table\Generated;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\UserScopeTable
{
    public function deleteAllFromUser(int $userId): void
    {
        $sql = 'DELETE FROM fusio_user_scope
                      WHERE user_id = :id';

        $this->connection->executeQuery($sql, array('id' => $userId));
    }

    public function getValidScopes(int $userId, array $scopes): array
    {
        $result = $this->getAvailableScopes($userId);
        $data   = array();

        foreach ($result as $scope) {
            if (in_array($scope['name'], $scopes)) {
                $data[] = $scope;
            }
        }

        return $data;
    }

    public function getAvailableScopes(int $userId): array
    {
        $assignedScopes = $this->getScopesForUser($userId);

        // get scopes from plan
        $planId = (int) $this->connection->fetchOne('SELECT plan_id FROM fusio_user WHERE id = :user_id', ['user_id' => $userId]);
        if (!empty($planId)) {
            $assignedScopes = array_merge($assignedScopes, $this->getScopesForPlan($planId));
        }

        $scopes = [];
        foreach ($assignedScopes as $assignedScope) {
            $scopes[$assignedScope['name']] = $assignedScope;

            if (strpos($assignedScope['name'], '.') === false) {
                // load all sub scopes
                $sql = 'SELECT scope.id,
                               scope.name,
                               scope.description
                          FROM fusio_scope scope
                         WHERE scope.name LIKE :name';
                $subScopes = $this->connection->fetchAll($sql, ['name' => $assignedScope['name'] . '.%']);
                foreach ($subScopes as $subScope) {
                    $scopes[$subScope['name']] = $subScope;
                }
            }
        }

        return array_values($scopes);
    }

    private function getScopesForUser(int $userId): array
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_user_scope user_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = user_scope.scope_id
                     WHERE user_scope.user_id = :user_id
                  ORDER BY scope.id ASC';
        return $this->connection->fetchAll($sql, ['user_id' => $userId]) ?: [];
    }

    private function getScopesForPlan(int $planId): array
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_plan_scope plan_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = plan_scope.scope_id
                     WHERE plan_scope.user_id = :plan_id
                  ORDER BY scope.id ASC';
        return $this->connection->fetchAll($sql, ['plan_id' => $planId]) ?: [];
    }
}
