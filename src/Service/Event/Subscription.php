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

namespace Fusio\Impl\Service\Event;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Event\Subscription\CreatedEvent;
use Fusio\Impl\Event\Event\Subscription\DeletedEvent;
use Fusio\Impl\Event\Event\Subscription\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\EventSubscriptionCreate;
use Fusio\Model\Backend\EventSubscriptionUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Subscription
{
    private Table\Event\Subscription $subscriptionTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Event\Subscription $subscriptionTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->subscriptionTable = $subscriptionTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(EventSubscriptionCreate $subscription, UserContext $context): int
    {
        // create subscription
        try {
            $this->subscriptionTable->beginTransaction();

            $row = new Table\Generated\EventSubscriptionRow();
            $row->setEventId($subscription->getEventId());
            $row->setUserId($subscription->getUserId());
            $row->setStatus(Table\Event\Subscription::STATUS_ACTIVE);
            $row->setEndpoint($subscription->getEndpoint());
            $this->subscriptionTable->create($row);

            $subscriptionId = $this->subscriptionTable->getLastInsertId();
            $subscription->setId($subscriptionId);

            $this->subscriptionTable->commit();
        } catch (\Throwable $e) {
            $this->subscriptionTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($subscription, $context));

        return $subscriptionId;
    }

    public function update(int $subscriptionId, EventSubscriptionUpdate $subscription, UserContext $context): int
    {
        $existing = $this->subscriptionTable->find($subscriptionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        // update subscription
        $existing->setEndpoint($subscription->getEndpoint());
        $this->subscriptionTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($subscription, $existing, $context));

        return $subscriptionId;
    }

    public function delete(int $subscriptionId, UserContext $context): int
    {
        $existing = $this->subscriptionTable->find($subscriptionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        // delete all responses
        $this->subscriptionTable->deleteAllResponses($subscriptionId);

        // remove subscription
        $this->subscriptionTable->delete($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $subscriptionId;
    }
}
