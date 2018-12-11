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

namespace Fusio\Impl\Table\Event;

use PSX\Sql\TableAbstract;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Subscription extends TableAbstract
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public function getName()
    {
        return 'fusio_event_subscription';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'event_id' => self::TYPE_INT,
            'user_id' => self::TYPE_INT,
            'status' => self::TYPE_INT,
            'endpoint' => self::TYPE_VARCHAR,
        );
    }

    public function getSubscriptionsForEvent($eventId)
    {
        $sql = 'SELECT id
                  FROM fusio_event_subscription
                 WHERE event_id = :event_id
                   AND status = :status
              ORDER BY id ASC';

        return $this->connection->fetchAll($sql, [
            'event_id' => $eventId,
            'status'   => self::STATUS_ACTIVE,
        ]);
    }

    public function getSubscriptionCount($userId)
    {
        return $this->connection->fetchColumn('SELECT COUNT(*) AS cnt FROM fusio_event_subscription WHERE user_id = :user_id', [
            'user_id' => $userId
        ]);
    }

    public function deleteAllResponses($subscriptionId)
    {
        return $this->connection->executeUpdate('DELETE FROM fusio_event_response WHERE subscription_id = :subscription_id', [
            'subscription_id' => $subscriptionId
        ]);
    }
}
