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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Table;
use PSX\Sql\Reference;
use PSX\Sql\ViewAbstract;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Subscription extends ViewAbstract
{
    public function getCollection($userId, $startIndex = 0)
    {
        $sql = '    SELECT event_subscription.id,
                           event_subscription.status,
                           event_subscription.endpoint,
                           event.name
                      FROM fusio_event_subscription event_subscription
                INNER JOIN fusio_event event
                        ON event_subscription.event_id = event.id
                     WHERE event_subscription.user_id = :user_id';

        $definition = [
            'entry' => $this->doCollection($sql, ['user_id' => $userId], [
                'id' => $this->fieldInteger('id'),
                'status' => $this->fieldInteger('status'),
                'event' => 'name',
                'endpoint' => 'endpoint',
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($userId, $subscriptionId)
    {
        $sql = '    SELECT event_subscription.id,
                           event_subscription.status,
                           event_subscription.endpoint,
                           event.name
                      FROM fusio_event_subscription event_subscription
                INNER JOIN fusio_event event
                        ON event_subscription.event_id = event.id
                     WHERE event_subscription.id = :id
                       AND event_subscription.user_id = :user_id';

        $definition = $this->doEntity($sql, ['user_id' => $userId, 'id' => $subscriptionId], [
            'id' => $this->fieldInteger('id'),
            'status' => $this->fieldInteger('status'),
            'event' => 'name',
            'endpoint' => 'endpoint',
            'responses' => $this->doCollection([$this->getTable(Table\Event\Response::class), 'getAllBySubscription'], [$userId, new Reference('id')], [
                'status' => $this->fieldInteger('status'),
                'code' => $this->fieldInteger('code'),
                'attempts' => $this->fieldInteger('attempts'),
                'executeDate' => $this->fieldDateTime('execute_date'),
            ]),
        ]);

        return $this->build($definition);
    }
}
