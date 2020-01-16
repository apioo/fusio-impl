<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Contract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Contract extends ViewAbstract
{
    public function getCollection($userId, $startIndex = 0)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = new Condition();
        $condition->equals('user_id', $userId);
        $condition->in('status', [Table\Plan\Contract::STATUS_ACTIVE, Table\Plan\Contract::STATUS_CLOSED, Table\Plan\Contract::STATUS_CANCELLED]);

        $definition = [
            'totalResults' => $this->getTable(Table\Plan\Contract::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Plan\Contract::class), 'getAll'], [$startIndex, $count, 'id', Sql::SORT_DESC, $condition], [
                'id' => $this->fieldInteger('id'),
                'status' => $this->fieldInteger('status'),
                'plan' => $this->doEntity([$this->getTable(Table\Plan::class), 'get'], [new Reference('plan_id')], [
                    'id' => $this->fieldInteger('id'),
                    'name' => 'name',
                    'description' => 'description',
                ]),
                'amount' => $this->fieldNumber('amount'),
                'points' => $this->fieldInteger('points'),
                'period' => $this->fieldInteger('period_type'),
                'insertDate' => $this->fieldDateTime('insert_date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($userId, $contractId)
    {
        $condition = new Condition();
        $condition->equals('id', $contractId);
        $condition->equals('user_id', $userId);

        $definition = $this->doEntity([$this->getTable(Table\Plan\Contract::class), 'getOneBy'], [$condition], [
            'id' => $this->fieldInteger('id'),
            'status' => $this->fieldInteger('status'),
            'plan' => $this->doEntity([$this->getTable(Table\Plan::class), 'get'], [new Reference('id')], [
                'id' => $this->fieldInteger('id'),
                'name' => 'name',
                'description' => 'description',
            ]),
            'amount' => $this->fieldNumber('amount'),
            'points' => $this->fieldInteger('points'),
            'period' => $this->fieldInteger('period_type'),
            'insertDate' => $this->fieldDateTime('insert_date'),
        ]);

        return $this->build($definition);
    }
}
