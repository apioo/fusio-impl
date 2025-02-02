<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Table\Plan;

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
class Scope extends Generated\PlanScopeTable
{
    public function deleteAllFromPlan(int $planId)
    {
        $sql = 'DELETE FROM fusio_plan_scope
                      WHERE plan_id = :id';

        $this->connection->executeQuery($sql, ['id' => $planId]);
    }

    public function getAvailableScopes(?string $tenantId, int $planId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Generated\ScopeTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_PLAN_ID, $planId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'scope.' . Generated\ScopeTable::COLUMN_ID,
                'scope.' . Generated\ScopeTable::COLUMN_NAME,
                'scope.' . Generated\ScopeTable::COLUMN_DESCRIPTION,
            ])
            ->from('fusio_plan_scope', 'plan_scope')
            ->innerJoin('plan_scope', 'fusio_scope', 'scope', 'plan_scope.' . self::COLUMN_SCOPE_ID . ' = scope.' . Generated\ScopeTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('scope.' . self::COLUMN_ID, 'ASC')
            ->setParameters($condition->getValues());

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters()) ?: [];
    }
}
