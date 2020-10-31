<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Consumer\View\Plan;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Invoice
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Invoice extends ViewAbstract
{
    public function getCollection(int $userId, int $startIndex = 0, ?int $contractId = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = new Condition();
        $condition->equals('user_id', $userId);
        $condition->in('status', [Table\Plan\Invoice::STATUS_OPEN, Table\Plan\Invoice::STATUS_PAYED]);

        if (!empty($contractId)) {
            $condition->equals('contract_id', $contractId);
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Plan\Invoice::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Plan\Invoice::class), 'getAll'], [$startIndex, $count, 'id', Sql::SORT_DESC, $condition], [
                'id' => $this->fieldInteger('id'),
                'contractId' => $this->fieldInteger('contract_id'),
                'prevId' => $this->fieldInteger('prev_id'),
                'displayId' => 'display_id',
                'status' => $this->fieldInteger('status'),
                'amount' => $this->fieldNumber('amount'),
                'points' => $this->fieldInteger('points'),
                'fromDate' => $this->fieldDateTime('from_date'),
                'toDate' => $this->fieldDateTime('to_date'),
                'payDate' => $this->fieldDateTime('pay_date'),
                'insertDate' => $this->fieldDateTime('insert_date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($userId, $invoiceId)
    {
        $condition = new Condition();
        $condition->equals('id', $invoiceId);
        $condition->equals('user_id', $userId);

        $definition = $this->doEntity([$this->getTable(Table\Plan\Invoice::class), 'getOneBy'], [$condition], [
            'id' => $this->fieldInteger('id'),
            'contractId' => $this->fieldInteger('contract_id'),
            'prevId' => $this->fieldInteger('prev_id'),
            'displayId' => 'display_id',
            'status' => $this->fieldInteger('status'),
            'amount' => $this->fieldNumber('amount'),
            'points' => $this->fieldInteger('points'),
            'fromDate' => $this->fieldDateTime('from_date'),
            'toDate' => $this->fieldDateTime('to_date'),
            'payDate' => $this->fieldDateTime('pay_date'),
            'insertDate' => $this->fieldDateTime('insert_date'),
        ]);

        return $this->build($definition);
    }
}
