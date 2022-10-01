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

namespace Fusio\Impl\Consumer\View;

use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;
use Fusio\Impl\Table;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Transaction extends ViewAbstract
{
    public function getCollection(int $userId, ?int $startIndex = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = new Condition();
        $condition->equals('user_id', $userId);

        $definition = [
            'totalResults' => $this->getTable(Table\Transaction::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Transaction::class), 'findAll'], [$condition, $startIndex, $count, 'id', Sql::SORT_DESC], [
                'id' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_ID),
                'userId' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_USER_ID),
                'planId' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_PLAN_ID),
                'transactionId' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID),
                'amount' => $this->fieldNumber(Table\Generated\TransactionTable::COLUMN_AMOUNT),
                'points' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_POINTS),
                'periodStart' => $this->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_START),
                'periodEnd' => $this->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_END),
                'insertDate' => $this->fieldDateTime(Table\Generated\TransactionTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(int $userId, int $transactionId)
    {
        $condition = new Condition();
        $condition->equals('id', $transactionId);
        $condition->equals('user_id', $userId);

        $definition = $this->doEntity([$this->getTable(Table\Transaction::class), 'findOneBy'], [$condition], [
            'id' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_ID),
            'userId' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_USER_ID),
            'planId' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_PLAN_ID),
            'plan' => $this->doEntity([$this->getTable(Table\Plan::class), 'find'], [new Reference('id')], [
                'id' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
                'description' => Table\Generated\PlanTable::COLUMN_DESCRIPTION,
                'price' => $this->fieldNumber(Table\Generated\PlanTable::COLUMN_PRICE),
                'points' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_POINTS),
                'period' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_PERIOD_TYPE),
            ]),
            'transactionId' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID),
            'amount' => $this->fieldNumber(Table\Generated\TransactionTable::COLUMN_AMOUNT),
            'points' => $this->fieldInteger(Table\Generated\TransactionTable::COLUMN_POINTS),
            'periodStart' => $this->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_START),
            'periodEnd' => $this->fieldDateTime(Table\Generated\TransactionTable::COLUMN_PERIOD_END),
            'insertDate' => $this->fieldDateTime(Table\Generated\TransactionTable::COLUMN_INSERT_DATE),
        ]);

        return $this->build($definition);
    }
}
