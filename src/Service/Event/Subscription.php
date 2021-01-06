<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Event;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Event\Subscription\CreatedEvent;
use Fusio\Impl\Event\Event\Subscription\DeletedEvent;
use Fusio\Impl\Event\Event\Subscription\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Event_Subscription_Create;
use Fusio\Model\Backend\Event_Subscription_Update;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Subscription
{
    /**
     * @var \Fusio\Impl\Table\Event
     */
    protected $eventTable;

    /**
     * @var \Fusio\Impl\Table\Event\Subscription
     */
    protected $subscriptionTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Event $eventTable
     * @param \Fusio\Impl\Table\Event\Subscription $subscriptionTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Event $eventTable, Table\Event\Subscription $subscriptionTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventTable        = $eventTable;
        $this->subscriptionTable = $subscriptionTable;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create(Event_Subscription_Create $subscription, UserContext $context)
    {
        // create event
        $record = [
            'event_id' => $subscription->getEventId(),
            'user_id'  => $subscription->getUserId(),
            'status'   => Table\Event\Subscription::STATUS_ACTIVE,
            'endpoint' => $subscription->getEndpoint(),
        ];

        $this->subscriptionTable->create($record);

        // get last insert id
        $subscriptionId = $this->subscriptionTable->getLastInsertId();
        $subscription->setId($subscriptionId);

        $this->eventDispatcher->dispatch(new CreatedEvent($subscription, $context));
    }

    public function update(int $subscriptionId, Event_Subscription_Update $subscription, UserContext $context)
    {
        $existing = $this->subscriptionTable->get($subscriptionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        // update subscription
        $record = [
            'id'       => $existing['id'],
            'endpoint' => $subscription->getEndpoint(),
        ];

        $this->subscriptionTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($subscription, $existing, $context));
    }

    public function delete(int $subscriptionId, UserContext $context)
    {
        $existing = $this->subscriptionTable->get($subscriptionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        // delete all responses
        $this->subscriptionTable->deleteAllResponses($subscriptionId);

        // remove subscription
        $record = [
            'id' => $existing['id'],
        ];

        $this->subscriptionTable->delete($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }
}
