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

namespace Fusio\Impl\Consumer\View\Event;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
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
    public function getCollection(int $userId, int $startIndex = 0, ?string $tenantId = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = Condition::withAnd();
        $condition->equals('event_subscription.user_id', $userId);
        if (!empty($tenantId)) {
            $condition->equals('event.tenant_id', $tenantId);
        }

        $countSql = $this->getBaseQuery(['COUNT(*) AS cnt'], $condition);
        $querySql = $this->getBaseQuery(['event_subscription.id', 'event_subscription.status', 'event_subscription.endpoint', 'event.name'], $condition);
        $querySql = $this->connection->getDatabasePlatform()->modifyLimitQuery($querySql, $count, $startIndex);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countSql, $condition->getValues(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($querySql, $condition->getValues(), [
                'id' => $builder->fieldInteger('id'),
                'status' => $builder->fieldInteger('status'),
                'event' => 'name',
                'endpoint' => 'endpoint',
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $userId, int $subscriptionId, ?string $tenantId = null)
    {
        $condition = Condition::withAnd();
        $condition->equals('event_subscription.id', $subscriptionId);
        $condition->equals('event_subscription.user_id', $userId);
        if (!empty($tenantId)) {
            $condition->equals('event.tenant_id', $tenantId);
        }

        $querySql = $this->getBaseQuery(['event_subscription.id', 'event_subscription.status', 'event_subscription.endpoint', 'event.name'], $condition, 'event_subscription.id DESC');
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity($querySql, $condition->getValues(), [
            'id' => $builder->fieldInteger('id'),
            'status' => $builder->fieldInteger('status'),
            'event' => 'name',
            'endpoint' => 'endpoint',
            'responses' => $builder->doCollection([$this->getTable(Table\Event\Response::class), 'getAllBySubscription'], [new Reference('id')], [
                'status' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_STATUS),
                'attempts' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_ATTEMPTS),
                'code' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_CODE),
                'body' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_BODY),
                'executeDate' => $builder->fieldDateTime(Table\Generated\EventResponseTable::COLUMN_EXECUTE_DATE),
            ]),
        ]);

        return $builder->build($definition);
    }

    private function getBaseQuery(array $fields, Condition $condition, ?string $orderBy = null): string
    {
        $fields  = implode(',', $fields);
        $where   = $condition->getStatement($this->connection->getDatabasePlatform());
        $orderBy = $orderBy !== null ? 'ORDER BY ' . $orderBy : '';

        return <<<SQL
    SELECT {$fields}
      FROM fusio_event_subscription event_subscription
INNER JOIN fusio_event event
        ON event_subscription.event_id = event.id
           {$where}
           {$orderBy}
SQL;
    }
}
