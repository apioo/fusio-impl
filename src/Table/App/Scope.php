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

namespace Fusio\Impl\Table\App;

use Fusio\Impl\Table\Generated;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\AppScopeTable
{
    public function deleteAllFromApp(int $appId): void
    {
        $sql = 'DELETE FROM fusio_app_scope
                      WHERE app_id = :app_id';

        $this->connection->executeQuery($sql, array('app_id' => $appId));
    }

    public function getValidScopes(int $appId, array $scopes): array
    {
        $result = $this->getAvailableScopes($appId, true);
        $data   = array();

        foreach ($result as $scope) {
            if (in_array($scope['name'], $scopes)) {
                $data[] = $scope;
            }
        }

        return $data;
    }

    public function getAvailableScopes(int $appId, bool $includePlanScopes = false): array
    {
        $assignedScopes = $this->getScopesForApp($appId);

        // get scopes from plan
        if ($includePlanScopes) {
            $assignedScopes = array_merge($assignedScopes, $this->getScopesForPlan($appId));
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
                         WHERE scope.name LIKE :name';
                $subScopes = $this->connection->fetchAllAssociative($sql, ['name' => $assignedScope['name'] . '.%']);
                foreach ($subScopes as $subScope) {
                    $scopes[$subScope['name']] = $subScope;
                }
            }
        }

        return array_values($scopes);
    }

    private function getScopesForApp(int $appId): array
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_app_scope app_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = app_scope.scope_id
                     WHERE app_scope.app_id = :app_id
                  ORDER BY scope.id ASC';
        return $this->connection->fetchAllAssociative($sql, ['app_id' => $appId]) ?: [];
    }

    private function getScopesForPlan(int $appId): array
    {
        $userId = (int) $this->connection->fetchOne('SELECT user_id FROM fusio_app WHERE id = :app_id', ['app_id' => $appId]);
        if (empty($userId)) {
            return [];
        }

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
