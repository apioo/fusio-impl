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

namespace Fusio\Impl\Backend\View\Event;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Subscription extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $sortBy = Table\Generated\EventSubscriptionTable::COLUMN_ID;

        $condition = new Condition();
        $condition->in(Table\Generated\EventSubscriptionTable::COLUMN_STATUS, [Table\Event\Subscription::STATUS_ACTIVE]);

        if (!empty($search)) {
            $condition->like(Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Event\Subscription::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Event\Subscription::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, Sql::SORT_DESC], [
                'id' => $this->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_ID),
                'eventId' => $this->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID),
                'userId' => $this->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_USER_ID),
                'endpoint' => Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Event\Subscription::class), 'find'], [$id], [
            'id' => $this->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_ID),
            'eventId' => $this->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID),
            'userId' => $this->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_USER_ID),
            'endpoint' => Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            'responses' => $this->doCollection([$this->getTable(Table\Event\Response::class), 'getAllBySubscription'], [new Reference('id')], [
                'id' => $this->fieldInteger(Table\Generated\EventResponseTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\EventResponseTable::COLUMN_STATUS),
                'code' => $this->fieldInteger(Table\Generated\EventResponseTable::COLUMN_CODE),
                'attempts' => $this->fieldInteger(Table\Generated\EventResponseTable::COLUMN_ATTEMPTS),
                'error' => Table\Generated\EventResponseTable::COLUMN_ERROR,
                'executeDate' => $this->fieldDateTime(Table\Generated\EventResponseTable::COLUMN_EXECUTE_DATE),
            ]),
        ]);

        return $this->build($definition);
    }
}
