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
 * @link    https://www.fusio-project.org
 */
class Rate extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\RateTable::COLUMN_PRIORITY;
        }

        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_DESC;
        }

        $condition = new Condition();
        $condition->in(Table\Generated\RateTable::COLUMN_STATUS, [Table\Rate::STATUS_ACTIVE]);

        if (!empty($search)) {
            $condition->like(Table\Generated\RateTable::COLUMN_NAME, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Rate::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Rate::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\RateTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\RateTable::COLUMN_STATUS),
                'priority' => $this->fieldInteger(Table\Generated\RateTable::COLUMN_PRIORITY),
                'name' => Table\Generated\RateTable::COLUMN_NAME,
                'rateLimit' => Table\Generated\RateTable::COLUMN_RATE_LIMIT,
                'timespan' => Table\Generated\RateTable::COLUMN_TIMESPAN,
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(string $id)
    {
        if (str_starts_with($id, '~')) {
            $method = 'findOneByName';
            $id = urldecode(substr($id, 1));
        } else {
            $method = 'find';
            $id = (int) $id;
        }

        $definition = $this->doEntity([$this->getTable(Table\Rate::class), $method], [$id], [
            'id' => $this->fieldInteger(Table\Generated\RateTable::COLUMN_ID),
            'status' => $this->fieldInteger(Table\Generated\RateTable::COLUMN_STATUS),
            'priority' => $this->fieldInteger(Table\Generated\RateTable::COLUMN_PRIORITY),
            'name' => Table\Generated\RateTable::COLUMN_NAME,
            'rateLimit' => Table\Generated\RateTable::COLUMN_RATE_LIMIT,
            'timespan' => Table\Generated\RateTable::COLUMN_TIMESPAN,
            'allocation' => $this->doCollection([$this->getTable(Table\Rate\Allocation::class), 'findByRateId'], [new Reference('id')], [
                'id' => $this->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_ID),
                'rateId' => $this->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_RATE_ID),
                'routeId' => $this->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_ROUTE_ID),
                'appId' => $this->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_APP_ID),
                'authenticated' => $this->fieldBoolean(Table\Generated\RateAllocationTable::COLUMN_AUTHENTICATED),
                'parameters' => Table\Generated\RateAllocationTable::COLUMN_PARAMETERS,
            ]),
        ]);

        return $this->build($definition);
    }
}
