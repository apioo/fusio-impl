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

namespace Fusio\Impl\Table\App;

use Fusio\Impl\Table\Generated;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

    public function getValidScopes(?string $tenantId, int $appId, array $scopes): array
    {
        $result = $this->getAvailableScopes($tenantId, $appId, true);
        $data   = array();

        foreach ($result as $scope) {
            if (in_array($scope['name'], $scopes)) {
                $data[] = $scope;
            }
        }

        return $data;
    }

    public function getAvailableScopes(?string $tenantId, int $appId, bool $includePlanScopes = false): array
    {
        $assignedScopes = $this->getScopesForApp($tenantId, $appId);

        // get scopes from plan
        if ($includePlanScopes) {
            $assignedScopes = array_merge($assignedScopes, $this->getScopesForPlan($tenantId, $appId));
        }

        $scopes = [];
        foreach ($assignedScopes as $assignedScope) {
            $scopes[$assignedScope['name']] = $assignedScope;

            if (!str_contains($assignedScope['name'], '.')) {
                // load all sub scopes
                $subScopes = $this->getTable(Table\Scope::class)->findSubScopes($tenantId, $assignedScope['name']);
                foreach ($subScopes as $subScope) {
                    $scopes[$subScope['name']] = $subScope;
                }
            }
        }

        return array_values($scopes);
    }

    private function getScopesForApp(?string $tenantId, int $appId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Generated\ScopeTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Generated\ScopeTable::COLUMN_STATUS, Table\Scope::STATUS_ACTIVE);
        $condition->equals(self::COLUMN_APP_ID, $appId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'scope.' . Generated\ScopeTable::COLUMN_ID,
                'scope.' . Generated\ScopeTable::COLUMN_NAME,
                'scope.' . Generated\ScopeTable::COLUMN_DESCRIPTION,
            ])
            ->from('fusio_app_scope', 'app_scope')
            ->innerJoin('app_scope', 'fusio_scope', 'scope', 'scope.' . Generated\ScopeTable::COLUMN_ID . ' = app_scope.' . self::COLUMN_SCOPE_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('scope.' . Generated\ScopeTable::COLUMN_ID, 'ASC')
            ->setParameters($condition->getValues());

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters()) ?: [];
    }

    private function getScopesForPlan(?string $tenantId, int $appId): array
    {
        $userId = $this->getTable(Table\App::class)->getUserId($tenantId, $appId);
        if (empty($userId)) {
            return [];
        }

        $planId = $this->getTable(Table\User::class)->getPlanId($tenantId, $appId);
        if (empty($planId)) {
            return [];
        }

        $condition = Condition::withAnd();
        $condition->equals(Generated\ScopeTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Generated\ScopeTable::COLUMN_STATUS, Table\Scope::STATUS_ACTIVE);
        $condition->equals(Generated\PlanScopeTable::COLUMN_PLAN_ID, $planId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'scope.' . Generated\ScopeTable::COLUMN_ID,
                'scope.' . Generated\ScopeTable::COLUMN_NAME,
                'scope.' . Generated\ScopeTable::COLUMN_DESCRIPTION,
            ])
            ->from('fusio_plan_scope', 'plan_scope')
            ->innerJoin('plan_scope', 'fusio_scope', 'scope', 'scope.' . Generated\ScopeTable::COLUMN_ID . ' = plan_scope.' . Generated\PlanScopeTable::COLUMN_SCOPE_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('scope.' . Generated\ScopeTable::COLUMN_ID, 'ASC')
            ->setParameters($condition->getValues());

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters()) ?: [];
    }
}
