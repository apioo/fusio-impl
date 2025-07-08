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

namespace Fusio\Impl\Backend\View\Dashboard;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * LatestTransactions
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LatestTransactions extends ViewAbstract
{
    public function getView(ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\TransactionTable::COLUMN_TENANT_ID, $context->getTenantId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'trans.' . Table\Generated\TransactionTable::COLUMN_ID,
                'trans.' . Table\Generated\TransactionTable::COLUMN_USER_ID,
                'trans.' . Table\Generated\TransactionTable::COLUMN_PLAN_ID,
                'trans.' . Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID,
                'trans.' . Table\Generated\TransactionTable::COLUMN_AMOUNT,
                'trans.' . Table\Generated\TransactionTable::COLUMN_INSERT_DATE,
            ])
            ->from('fusio_transaction', 'trans')
            ->orderBy('trans.' . Table\Generated\TransactionTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult(0)
            ->setMaxResults(6);

        $builder = new Builder($this->connection);

        $definition = [
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_ID),
                'user_id' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_USER_ID),
                'plan_id' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_PLAN_ID),
                'transactionId' => Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID,
                'amount' => $builder->fieldCallback(Table\Generated\TransactionTable::COLUMN_AMOUNT, function($value){
                    return round($value / 100, 2);
                }),
                'date' => $builder->fieldDateTime(Table\Generated\TransactionTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }
}
