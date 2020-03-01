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
    protected $subscriptionService;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @var \Fusio\Impl\Table\Event\Subscription
     */
    protected $subscriptionTable;

    /**
     * @var \Fusio\Impl\Table\Event
     */
    protected $eventTable;

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

    public function create($event, $endpoint, UserContext $context)
    {
        // check max subscription count
        $count = $this->subscriptionTable->getSubscriptionCount($context->getUserId());

        if ($count > $this->configService->getValue('consumer_subscription')) {
            throw new StatusCode\BadRequestException('Max subscription count reached');
        }

        // check whether the event exists
        $condition  = new Condition();
        $condition->equals('name', $event);

        $event = $this->eventTable->getOneBy($condition);

        if (empty($event)) {
            throw new StatusCode\BadRequestException('Event does not exist');
        }

        $this->subscriptionService->create(
            $event['id'],
            $context->getUserId(),
            $endpoint,
            $context
        );
    }

    public function update($subscriptionId, $endpoint, UserContext $context)
    {
        $subscription = $this->subscriptionTable->get($subscriptionId);

        if (empty($subscription)) {
            throw new StatusCode\NotFoundException('Could not find subscription');
        }

        if ($subscription['user_id'] != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Subscription does not belong to the user');
        }

        $this->subscriptionService->update($subscriptionId, $endpoint, $context);
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
