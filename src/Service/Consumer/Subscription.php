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

namespace Fusio\Impl\Service\Consumer;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Consumer\EventSubscriptionCreate;
use Fusio\Model\Consumer\EventSubscriptionUpdate;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Subscription
{
    private Service\Event\Subscription $subscriptionService;
    private Service\Config $configService;
    private Table\Event\Subscription $subscriptionTable;
    private Table\Event $eventTable;

    public function __construct(Service\Event\Subscription $subscriptionService, Service\Config $configService, Table\Event\Subscription $subscriptionTable, Table\Event $eventTable)
    {
        $this->subscriptionService = $subscriptionService;
        $this->configService       = $configService;
        $this->subscriptionTable   = $subscriptionTable;
        $this->eventTable          = $eventTable;
    }

    public function create(EventSubscriptionCreate $subscription, UserContext $context): int
    {
        // check max subscription count
        $count = $this->subscriptionTable->getSubscriptionCount($context->getUserId());
        if ($count > $this->configService->getValue('consumer_subscription')) {
            throw new StatusCode\BadRequestException('Max subscription count reached');
        }

        // check whether the event exists
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\EventTable::COLUMN_NAME, $subscription->getEvent());

        $event = $this->eventTable->findOneBy($condition);
        if (empty($event)) {
            throw new StatusCode\BadRequestException('Event does not exist');
        }

        $this->assertUrl($subscription->getEndpoint());

        $backendSubscription = new Model\Backend\EventSubscriptionCreate();
        $backendSubscription->setUserId($context->getUserId());
        $backendSubscription->setEventId($event->getId());
        $backendSubscription->setEndpoint($subscription->getEndpoint());

        return $this->subscriptionService->create($backendSubscription, $context);
    }

    public function update(int $subscriptionId, EventSubscriptionUpdate $subscription, UserContext $context): int
    {
        $existing = $this->subscriptionTable->find($subscriptionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Subscription does not belong to the user');
        }

        $this->assertUrl($subscription->getEndpoint());

        $backendSubscription = new Model\Backend\EventSubscriptionUpdate();
        $backendSubscription->setEndpoint($subscription->getEndpoint());

        return $this->subscriptionService->update($subscriptionId, $backendSubscription, $context);
    }

    public function delete(int $subscriptionId, UserContext $context): int
    {
        $subscription = $this->subscriptionTable->find($subscriptionId);
        if (empty($subscription)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        if ($subscription->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Subscription does not belong to the user');
        }

        return $this->subscriptionService->delete($subscriptionId, $context);
    }

    private function assertUrl(?string $url): void
    {
        if (empty($url)) {
            throw new StatusCode\BadRequestException('The endpoint contains no value');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new StatusCode\BadRequestException('The endpoint has an invalid url format');
        }
    }
}
