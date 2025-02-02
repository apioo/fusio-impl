<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * WebhookEventHandler
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
#[AsMessageHandler]
class WebhookSendHandler
{
    private Table\Webhook $webhookTable;
    private Table\Webhook\Response $responseTable;
    private Table\Event $eventTable;
    private MessageBusInterface $messageBus;

    public function __construct(Table\Webhook $webhookTable, Table\Webhook\Response $responseTable, Table\Event $eventTable, MessageBusInterface $messageBus)
    {
        $this->webhookTable = $webhookTable;
        $this->responseTable = $responseTable;
        $this->eventTable = $eventTable;
        $this->messageBus = $messageBus;
    }

    public function __invoke(TriggerEvent $event): void
    {
        $existing = $this->eventTable->findOneByTenantAndName($event->getTenantId(), null, $event->getEventName());
        if (!$existing instanceof Table\Generated\EventRow) {
            return;
        }

        $webhooks = $this->webhookTable->getWebhooksForEvent($existing->getId());
        foreach ($webhooks as $webhook) {
            $row = new Table\Generated\WebhookResponseRow();
            $row->setWebhookId($webhook['id']);
            $row->setStatus(Table\Webhook\Response::STATUS_PENDING);
            $row->setAttempts(0);
            $row->setInsertDate(LocalDateTime::now());
            $this->responseTable->create($row);

            $responseId = $this->responseTable->getLastInsertId();

            $this->messageBus->dispatch(new SendHttpRequest($responseId, $webhook['endpoint'], $event->getPayload()));
        }
    }
}
