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

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Backend\Filter\Transaction\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\OrderBy;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Transaction extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, QueryFilter $filter)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $sortBy = Table\Generated\TransactionTable::COLUMN_ID;

        $condition = $filter->getCondition();
        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Transaction::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Transaction::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, OrderBy::DESC], [
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

    public function getEntity(int $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Transaction::class), 'find'], [$id], [
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
