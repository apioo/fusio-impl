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

namespace Fusio\Impl\Table;

use Fusio\Impl\Table\Generated\OperationRow;
use Fusio\Impl\Table\Generated\PageRow;
use Fusio\Impl\Table\Generated\PlanRow;
use PSX\Sql\Condition;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Plan extends Generated\PlanTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    public function findOneByIdentifier(?string $tenantId, string $id): ?PlanRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndName($tenantId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, int $id): ?PlanRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, string $name): ?PlanRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }

    /**
     * Returns an array of plans which are currently active for the provided user
     *
     * @return PlanRow[]
     */
    public function getActivePlansForUser(?string $tenantId, int $userId): array
    {
        $now = new \DateTime();

        $condition = Condition::withAnd();
        $condition->equals('plan.' . self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals('trx.' . Generated\TransactionTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals('trx.' . Generated\TransactionTable::COLUMN_USER_ID, $userId);
        $condition->lessThan('trx.' . Generated\TransactionTable::COLUMN_PERIOD_START, $now->format($this->connection->getDatabasePlatform()->getDateTimeFormatString()));
        $condition->greaterThan('trx.' . Generated\TransactionTable::COLUMN_PERIOD_END, $now->format($this->connection->getDatabasePlatform()->getDateTimeFormatString()));

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'plan.*',
            ])
            ->from('fusio_transaction', 'trx')
            ->innerJoin('trx', 'fusio_plan', 'plan', 'plan.' . self::COLUMN_ID . ' = trx.' . Generated\TransactionTable::COLUMN_PLAN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        $plans = [];
        foreach ($result as $row) {
            $plans[] = $this->newRecord($row);
        }
        return $plans;
    }
}
