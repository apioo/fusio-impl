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

namespace Fusio\Impl\Consumer\View\Event;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Reference;
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
    public function getCollection(int $userId, int $startIndex = 0)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = new Condition();
        $condition->equals('event_subscription.user_id', $userId);

        $countSql = $this->getBaseQuery(['COUNT(*) AS cnt'], $condition);
        $querySql = $this->getBaseQuery(['event_subscription.id', 'event_subscription.status', 'event_subscription.endpoint', 'event.name'], $condition);
        $querySql = $this->connection->getDatabasePlatform()->modifyLimitQuery($querySql, $count, $startIndex);

        $definition = [
            'totalResults' => $this->doValue($countSql, $condition->getValues(), $this->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection($querySql, $condition->getValues(), [
                'id' => $this->fieldInteger('id'),
                'status' => $this->fieldInteger('status'),
                'event' => 'name',
                'endpoint' => 'endpoint',
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(int $userId, int $subscriptionId)
    {
        $condition = new Condition();
        $condition->equals('event_subscription.id', $subscriptionId);
        $condition->equals('event_subscription.user_id', $userId);

        $querySql = $this->getBaseQuery(['event_subscription.id', 'event_subscription.status', 'event_subscription.endpoint', 'event.name'], $condition, 'event_subscription.id DESC');

        $definition = $this->doEntity($querySql, $condition->getValues(), [
            'id' => $this->fieldInteger('id'),
            'status' => $this->fieldInteger('status'),
            'event' => 'name',
            'endpoint' => 'endpoint',
            'responses' => $this->doCollection([$this->getTable(Table\Event\Response::class), 'getAllBySubscription'], [new Reference('id')], [
                'status' => $this->fieldInteger('status'),
                'code' => $this->fieldInteger('code'),
                'attempts' => $this->fieldInteger('attempts'),
                'executeDate' => $this->fieldDateTime('execute_date'),
            ]),
        ]);

        return $this->build($definition);
    }

    /**
     * @param array $fields
     * @param \PSX\Sql\Condition $condition
     * @param string $orderBy
     * @return string
     */
    private function getBaseQuery(array $fields, Condition $condition, $orderBy = null)
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
