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

namespace Fusio\Impl\Table\Rate;

use Fusio\Engine\Model;
use Fusio\Impl\Table\Generated;
use Fusio\Impl\Table\Rate;
use PSX\Sql\Condition;

/**
 * Allocation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Allocation extends Generated\RateAllocationTable
{
    public function deleteAllFromRate($rateId): void
    {
        $sql = 'DELETE FROM fusio_rate_allocation 
                      WHERE rate_id = :rate_id';

        $this->connection->executeStatement($sql, ['rate_id' => $rateId]);
    }

    public function getRateForRequest(?string $tenantId, Generated\OperationRow $operation, Model\AppInterface $app, Model\UserInterface $user): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Generated\RateTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Generated\RateTable::COLUMN_STATUS, Rate::STATUS_ACTIVE);
        $condition->add(Condition::withOr()
            ->nil('rate_allocation.' . self::COLUMN_OPERATION_ID)
            ->equals('rate_allocation.' . self::COLUMN_OPERATION_ID, $operation->getId()));
        $condition->add(Condition::withOr()
            ->nil('rate_allocation.' . self::COLUMN_USER_ID)
            ->equals('rate_allocation.' . self::COLUMN_USER_ID, $user->getId()));
        $condition->add(Condition::withOr()
            ->nil('rate_allocation.' . self::COLUMN_PLAN_ID)
            ->equals('rate_allocation.' . self::COLUMN_PLAN_ID, $user->getPlanId()));
        $condition->add(Condition::withOr()
            ->nil('rate_allocation.' . self::COLUMN_APP_ID)
            ->equals('rate_allocation.' . self::COLUMN_APP_ID, $app->getId()));
        $condition->add(Condition::withOr()
            ->nil('rate_allocation.' . self::COLUMN_AUTHENTICATED)
            ->equals('rate_allocation.' . self::COLUMN_AUTHENTICATED, $user->isAnonymous() ? 0 : 1));

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'rate.' . Generated\RateTable::COLUMN_RATE_LIMIT,
                'rate.' . Generated\RateTable::COLUMN_TIMESPAN,
            ])
            ->from('fusio_rate_allocation', 'rate_allocation')
            ->innerJoin('rate_allocation', 'fusio_rate', 'rate', 'rate_allocation.' . self::COLUMN_RATE_ID . ' = rate.' . Generated\RateTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('rate.' . Generated\RateTable::COLUMN_PRIORITY, 'DESC')
            ->setParameters($condition->getValues());

        $row = $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
        if (empty($row)) {
            throw new \RuntimeException('Could not find rate for request');
        }

        return $row;
    }
}
