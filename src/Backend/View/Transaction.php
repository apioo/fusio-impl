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

namespace Fusio\Impl\Backend\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Backend\Filter\Transaction\TransactionQueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Transaction extends ViewAbstract
{
    public function getCollection(TransactionQueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\TransactionColumn::tryFrom($filter->getSortBy(Table\Generated\TransactionTable::COLUMN_ID) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID, DateQueryFilter::COLUMN_DATE => Table\Generated\TransactionTable::COLUMN_INSERT_DATE]);
        $condition->equals(Table\Generated\TransactionTable::COLUMN_TENANT_ID, $context->getTenantId());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Transaction::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Transaction::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_ID),
                'userId' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_USER_ID),
                'planId' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_PLAN_ID),
                'transactionId' => Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID,
                'amount' => $builder->fieldCallback(Table\Generated\TransactionTable::COLUMN_AMOUNT, function($value){
                    return round($value / 100, 2);
                }),
                'points' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_POINTS),
                'periodStart' => $builder->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_START),
                'periodEnd' => $builder->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_END),
                'insertDate' => $builder->fieldDateTime(Table\Generated\TransactionTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Transaction::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_ID),
            'userId' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_USER_ID),
            'planId' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_PLAN_ID),
            'transactionId' => Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID,
            'amount' => $builder->fieldCallback(Table\Generated\TransactionTable::COLUMN_AMOUNT, function($value){
                return round($value / 100, 2);
            }),
            'points' => $builder->fieldInteger(Table\Generated\TransactionTable::COLUMN_POINTS),
            'periodStart' => $builder->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_START),
            'periodEnd' => $builder->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_END),
            'insertDate' => $builder->fieldDateTime(Table\Generated\TransactionTable::COLUMN_INSERT_DATE),
        ]);

        return $builder->build($definition);
    }
}
