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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Plan extends ViewAbstract
{
    public function getCollection(int $userId, int $startIndex = 0)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = new Condition();
        $condition->equals('status', Table\Plan::STATUS_ACTIVE);

        $definition = [
            'totalResults' => $this->getTable(Table\Plan::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Plan::class), 'findAll'], [$condition, $startIndex, $count, 'price', Sql::SORT_ASC], [
                'id' => $this->fieldInteger('id'),
                'name' => 'name',
                'description' => 'description',
                'price' => $this->fieldNumber('price'),
                'points' => $this->fieldInteger('points'),
                'period' => $this->fieldInteger('period_type'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(int $userId, int $planId)
    {
        $condition = new Condition();
        $condition->equals('id', $planId);
        $condition->equals('status', Table\Plan::STATUS_ACTIVE);

        $definition = $this->doEntity([$this->getTable(Table\Plan::class), 'findOneBy'], [$condition], [
            'id' => $this->fieldInteger('id'),
            'name' => 'name',
            'description' => 'description',
            'price' => $this->fieldNumber('price'),
            'points' => $this->fieldInteger('points'),
            'period' => $this->fieldInteger('period_type'),
        ]);

        return $this->build($definition);
    }
}
