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

namespace Fusio\Impl\MessengerHandler;

use Fusio\Impl\Messenger\SendHttpRequest;
use Fusio\Impl\Messenger\TriggerEvent;
use Fusio\Impl\Table;
use PSX\DateTime\LocalDateTime;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * TriggerEventHandler
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
#[AsMessageHandler]
class TriggerEventHandler
{
    private Table\Event $eventTable;
    private Table\Event\Subscription $subscriptionTable;
    private Table\Event\Response $responseTable;
    private MessageBusInterface $messageBus;

    public function __construct(Table\Event $eventTable, Table\Event\Subscription $subscriptionTable, Table\Event\Response $responseTable, MessageBusInterface $messageBus)
    {
        $this->eventTable = $eventTable;
        $this->subscriptionTable = $subscriptionTable;
        $this->responseTable = $responseTable;
        $this->messageBus = $messageBus;
    }

    public function __invoke(TriggerEvent $event): void
    {
        $existing = $this->eventTable->findOneByName($event->getEventName());
        if (!$existing instanceof Table\Generated\EventRow) {
            return;
        }

        $subscriptions = $this->subscriptionTable->getSubscriptionsForEvent($existing->getId());
        foreach ($subscriptions as $subscription) {
            $row = new Table\Generated\EventResponseRow();
            $row->setSubscriptionId($subscription['id']);
            $row->setStatus(Table\Event\Response::STATUS_PENDING);
            $row->setAttempts(0);
            $row->setInsertDate(LocalDateTime::now());
            $this->responseTable->create($row);

            $responseId = $this->responseTable->getLastInsertId();

            $this->messageBus->dispatch(new SendHttpRequest($responseId, $subscription['endpoint'], $event->getPayload()));
        }
    }
}
