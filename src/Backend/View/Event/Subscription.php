<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Backend\View\Event;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

        $condition = Condition::withAnd();
        $condition->in(Table\Generated\EventSubscriptionTable::COLUMN_STATUS, [Table\Event\Subscription::STATUS_ACTIVE]);

        if (!empty($search)) {
            $condition->like(Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Event\Subscription::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Event\Subscription::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, OrderBy::DESC], [
                'id' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_ID),
                'eventId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID),
                'userId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_USER_ID),
                'endpoint' => Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Event\Subscription::class), 'find'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_ID),
            'eventId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID),
            'userId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_USER_ID),
            'endpoint' => Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            'responses' => $builder->doCollection([$this->getTable(Table\Event\Response::class), 'getAllBySubscription'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_STATUS),
                'attempts' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_ATTEMPTS),
                'code' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_CODE),
                'body' => Table\Generated\EventResponseTable::COLUMN_BODY,
                'executeDate' => $builder->fieldDateTime(Table\Generated\EventResponseTable::COLUMN_EXECUTE_DATE),
            ]),
        ]);

        return $builder->build($definition);
    }
}
