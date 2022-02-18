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

namespace Fusio\Impl\Backend\View\Plan;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Contract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Contract extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $sortBy = Table\Generated\PlanContractTable::COLUMN_ID;

        $condition = new Condition();
        $condition->in(Table\Generated\PlanContractTable::COLUMN_STATUS, [Table\Plan\Contract::STATUS_ACTIVE, Table\Plan\Contract::STATUS_CLOSED, Table\Plan\Contract::STATUS_CANCELLED]);

        if (!empty($search)) {
            $condition->like(Table\Generated\PlanContractTable::COLUMN_USER_ID, $search);
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Plan\Contract::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Plan\Contract::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, Sql::SORT_DESC], [
                'id' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_ID),
                'user' => $this->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                    'id' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                    'name' => Table\Generated\UserTable::COLUMN_NAME,
                ]),
                'plan' => $this->doEntity([$this->getTable(Table\Plan::class), 'find'], [new Reference('plan_id')], [
                    'id' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                    'name' => Table\Generated\PlanTable::COLUMN_NAME,
                ]),
                'status' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_STATUS),
                'amount' => $this->fieldNumber(Table\Generated\PlanContractTable::COLUMN_AMOUNT),
                'points' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_POINTS),
                'period' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_PERIOD_TYPE),
                'insertDate' => $this->fieldDateTime(Table\Generated\PlanContractTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Plan\Contract::class), 'find'], [$id], [
            'id' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_ID),
            'user' => $this->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                'id' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                'name' => Table\Generated\UserTable::COLUMN_NAME,
            ]),
            'plan' => $this->doEntity([$this->getTable(Table\Plan::class), 'find'], [new Reference('plan_id')], [
                'id' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
            ]),
            'status' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_STATUS),
            'amount' => $this->fieldNumber(Table\Generated\PlanContractTable::COLUMN_AMOUNT),
            'points' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_POINTS),
            'period' => $this->fieldInteger(Table\Generated\PlanContractTable::COLUMN_PERIOD_TYPE),
            'insertDate' => $this->fieldDateTime(Table\Generated\PlanContractTable::COLUMN_INSERT_DATE),

        ]);

        return $this->build($definition);
    }
}
