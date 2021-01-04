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

namespace Fusio\Impl\Service\Consumer;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Consumer\Event_Subscription_Create;
use Fusio\Model\Consumer\Event_Subscription_Update;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

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
     * @var \Fusio\Impl\Service\Event\Subscription
     */
    private $subscriptionService;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    private $configService;

    /**
     * @var \Fusio\Impl\Table\Event\Subscription
     */
    private $subscriptionTable;

    /**
     * @var \Fusio\Impl\Table\Event
     */
    private $eventTable;

    /**
     * @param \Fusio\Impl\Service\Event\Subscription $subscriptionService
     */
    public function __construct(Service\Event\Subscription $subscriptionService, Service\Config $configService, Table\Event\Subscription $subscriptionTable, Table\Event $eventTable)
    {
        $this->subscriptionService = $subscriptionService;
        $this->configService       = $configService;
        $this->subscriptionTable   = $subscriptionTable;
        $this->eventTable          = $eventTable;
    }

    public function create(Event_Subscription_Create $subscription, UserContext $context)
    {
        // check max subscription count
        $count = $this->subscriptionTable->getSubscriptionCount($context->getUserId());
        if ($count > $this->configService->getValue('consumer_subscription')) {
            throw new StatusCode\BadRequestException('Max subscription count reached');
        }

        // check whether the event exists
        $condition  = new Condition();
        $condition->equals('name', $subscription->getEvent());

        $event = $this->eventTable->getOneBy($condition);
        if (empty($event)) {
            throw new StatusCode\BadRequestException('Event does not exist');
        }

        $backendSubscription = new \Fusio\Model\Backend\Event_Subscription_Create();
        $backendSubscription->setUserId($context->getUserId());
        $backendSubscription->setEventId($event['id']);
        $backendSubscription->setEndpoint($subscription->getEndpoint());

        $this->subscriptionService->create($backendSubscription, $context);
    }

    public function update($subscriptionId, Event_Subscription_Update $subscription, UserContext $context)
    {
        $existing = $this->subscriptionTable->get($subscriptionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        if ($existing['user_id'] != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Subscription does not belong to the user');
        }

        $backendSubscription = new \Fusio\Model\Backend\Event_Subscription_Update();
        $backendSubscription->setEndpoint($subscription->getEndpoint());

        $this->subscriptionService->update($subscriptionId, $backendSubscription, $context);
    }

    public function delete($subscriptionId, UserContext $context)
    {
        $subscription = $this->subscriptionTable->get($subscriptionId);

        if (empty($subscription)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        if ($subscription['user_id'] != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Subscription does not belong to the user');
        }

        $this->subscriptionService->delete($subscriptionId, $context);
    }
}
