<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\View\Plan;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Invoice
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Invoice extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $sortBy = Table\Generated\PlanInvoiceTable::COLUMN_ID;

        $condition = new Condition();
        $condition->in(Table\Generated\PlanInvoiceTable::COLUMN_STATUS, [Table\Plan\Invoice::STATUS_OPEN, Table\Plan\Invoice::STATUS_PAYED]);

        if (!empty($search)) {
            $condition->like(Table\Generated\PlanInvoiceTable::COLUMN_DISPLAY_ID, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Plan\Invoice::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Plan\Invoice::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, Sql::SORT_DESC], [
                'id' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_ID),
                'contractId' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_CONTRACT_ID),
                'user' => $this->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference(Table\Generated\PlanInvoiceTable::COLUMN_USER_ID)], [
                    'id' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                    'name' => Table\Generated\UserTable::COLUMN_NAME,
                ]),
                'prevId' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_PREV_ID),
                'displayId' => Table\Generated\PlanInvoiceTable::COLUMN_DISPLAY_ID,
                'status' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_STATUS),
                'amount' => $this->fieldNumber(Table\Generated\PlanInvoiceTable::COLUMN_AMOUNT),
                'points' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_POINTS),
                'fromDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_FROM_DATE),
                'toDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_TO_DATE),
                'payDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_PAY_DATE),
                'insertDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Plan\Invoice::class), 'find'], [$id], [
            'id' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_ID),
            'contractId' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_CONTRACT_ID),
            'user' => $this->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference(Table\Generated\PlanInvoiceTable::COLUMN_USER_ID)], [
                'id' => $this->fieldInteger('id'),
                'name' => 'name',
            ]),
            'prevId' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_PREV_ID),
            'displayId' => Table\Generated\PlanInvoiceTable::COLUMN_DISPLAY_ID,
            'status' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_STATUS),
            'amount' => $this->fieldNumber(Table\Generated\PlanInvoiceTable::COLUMN_AMOUNT),
            'points' => $this->fieldInteger(Table\Generated\PlanInvoiceTable::COLUMN_POINTS),
            'fromDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_FROM_DATE),
            'toDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_TO_DATE),
            'payDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_PAY_DATE),
            'insertDate' => $this->fieldDateTime(Table\Generated\PlanInvoiceTable::COLUMN_INSERT_DATE),

        ]);

        return $this->build($definition);
    }
}
