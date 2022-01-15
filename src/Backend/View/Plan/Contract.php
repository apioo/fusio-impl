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

        $condition = new Condition();
        $condition->in('status', [Table\Plan\Contract::STATUS_ACTIVE, Table\Plan\Contract::STATUS_CLOSED, Table\Plan\Contract::STATUS_CANCELLED]);

        if (!empty($search)) {
            $condition->like('user_id', $search);
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Plan\Contract::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Plan\Contract::class), 'findAll'], [$condition, $startIndex, $count, 'id', Sql::SORT_DESC], [
                'id' => $this->fieldInteger('id'),
                'user' => $this->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                    'id' => $this->fieldInteger('id'),
                    'name' => 'name',
                ]),
                'plan' => $this->doEntity([$this->getTable(Table\Plan::class), 'find'], [new Reference('plan_id')], [
                    'id' => $this->fieldInteger('id'),
                    'name' => 'name',
                ]),
                'status' => $this->fieldInteger('status'),
                'amount' => $this->fieldNumber('amount'),
                'points' => $this->fieldInteger('points'),
                'period' => $this->fieldInteger('period_type'),
                'insertDate' => $this->fieldDateTime('insert_date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Plan\Contract::class), 'find'], [$id], [
            'id' => $this->fieldInteger('id'),
            'user' => $this->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                'id' => $this->fieldInteger('id'),
                'name' => 'name',
            ]),
            'plan' => $this->doEntity([$this->getTable(Table\Plan::class), 'find'], [new Reference('plan_id')], [
                'id' => $this->fieldInteger('id'),
                'name' => 'name',
            ]),
            'status' => $this->fieldInteger('status'),
            'amount' => $this->fieldNumber('amount'),
            'points' => $this->fieldInteger('points'),
            'period' => $this->fieldInteger('period_type'),
            'insertDate' => $this->fieldDateTime('insert_date'),

        ]);

        return $this->build($definition);
    }
}
