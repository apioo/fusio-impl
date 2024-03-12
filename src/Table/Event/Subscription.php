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

namespace Fusio\Impl\Table\Event;

use Fusio\Impl\Table\Generated;
use PSX\Sql\Condition;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Subscription extends Generated\EventSubscriptionTable
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public function getSubscriptionsForEvent(int $eventId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_EVENT_ID, $eventId);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'subscription.' . self::COLUMN_ID,
                'subscription.' . self::COLUMN_ENDPOINT,
            ])
            ->from('fusio_event_subscription', 'subscription')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('subscription.' . self::COLUMN_ID, 'ASC')
            ->setParameters($condition->getValues());

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    public function getSubscriptionCount(int $userId): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) AS cnt FROM fusio_event_subscription WHERE user_id = :user_id', [
            'user_id' => $userId
        ]);
    }

    public function deleteAllResponses(int $subscriptionId): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_event_response WHERE subscription_id = :subscription_id', [
            'subscription_id' => $subscriptionId
        ]);
    }
}
