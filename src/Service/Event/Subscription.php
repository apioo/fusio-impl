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

namespace Fusio\Impl\Service\Event;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Event\SubscribedEvent;
use Fusio\Impl\Event\Event\UnsubscribedEvent;
use Fusio\Impl\Event\EventEvents;
use Fusio\Impl\Service\Config;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
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
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Event $eventTable
     * @param \Fusio\Impl\Table\Event\Subscription $subscriptionTable
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Event $eventTable, Table\Event\Subscription $subscriptionTable, Config $configService, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventTable        = $eventTable;
        $this->subscriptionTable = $subscriptionTable;
        $this->configService     = $configService;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create($event, $endpoint, UserContext $context)
    {
        // check whether subscription exists
        $condition  = new Condition();
        $condition->equals('name', $event);

        $event = $this->eventTable->getOneBy($condition);

        if (empty($event)) {
            throw new StatusCode\BadRequestException('Event does not exist');
        }

        // check max subscription count
        $count = $this->subscriptionTable->getSubscriptionCount($context->getUserId());

        if ($count > $this->configService->getValue('consumer_subscription')) {
            throw new StatusCode\BadRequestException('Max subscription count reached');
        }

        // create event
        $record = [
            'event_id' => $event['id'],
            'user_id'  => $context->getUserId(),
            'status'   => Table\Event\Subscription::STATUS_ACTIVE,
            'endpoint' => $endpoint,
        ];

        $this->subscriptionTable->create($record);

        // get last insert id
        $subscriptionId = $this->subscriptionTable->getLastInsertId();

        $this->eventDispatcher->dispatch(EventEvents::SUBSCRIBE, new SubscribedEvent($subscriptionId, $record, $context));
    }

    public function update($subscriptionId, $endpoint, UserContext $context)
    {
        $subscription = $this->subscriptionTable->get($subscriptionId);

        if (empty($subscription)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        if ($subscription['user_id'] != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Subscription is not assigned to this account');
        }

        // update subscription
        $record = [
            'id'       => $subscription['id'],
            'endpoint' => $endpoint,
        ];

        $this->subscriptionTable->update($record);
    }

    public function delete($subscriptionId, UserContext $context)
    {
        $subscription = $this->subscriptionTable->get($subscriptionId);

        if (empty($subscription)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        if ($subscription['user_id'] != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Subscription is not assigned to this account');
        }

        // delete all responses
        $this->subscriptionTable->deleteAllResponses($subscriptionId);

        // remove subscription
        $record = [
            'id' => $subscription['id'],
        ];

        $this->subscriptionTable->delete($record);

        $this->eventDispatcher->dispatch(EventEvents::UNSUBSCRIBE, new UnsubscribedEvent($subscription['id'], $subscription, $context));
    }
}
