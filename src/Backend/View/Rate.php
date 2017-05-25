<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Rate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Rate extends ViewAbstract
{
    public function getCollection($startIndex = 0, $search = null)
    {
        $condition = new Condition();
        $condition->in('status', [Table\Rate::STATUS_ACTIVE]);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Rate::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $this->doCollection([$this->getTable(Table\Rate::class), 'getAll'], [$startIndex, 16, 'priority', Sql::SORT_DESC, $condition], [
                'id' => 'id',
                'status' => 'status',
                'priority' => 'priority',
                'name' => 'name',
                'rateLimit' => 'rateLimit',
                'timespan' => 'timespan',
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Rate::class), 'get'], [$id], [
            'id' => 'id',
            'status' => 'status',
            'priority' => 'priority',
            'name' => 'name',
            'rateLimit' => 'rateLimit',
            'timespan' => 'timespan',
            'allocation' => $this->doCollection([$this->getTable(Table\Rate\Allocation::class), 'getByRateId'], [new Reference('id')], [
                'id' => 'id',
                'rateId' => 'rateId',
                'routeId' => 'routeId',
                'appId' => 'appId',
                'authenticated' => 'authenticated',
                'parameters' => 'parameters',
            ]),
        ]);

        return $this->build($definition);
    }
}
