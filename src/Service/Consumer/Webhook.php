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
use Fusio\Model\Consumer\WebhookCreate;
use Fusio\Model\Consumer\WebhookUpdate;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Webhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Webhook
{
    private Service\Webhook $webhookService;
    private Service\Config $configService;
    private Table\Webhook $webhookTable;
    private Table\Event $eventTable;

    public function __construct(Service\Webhook $webhookService, Service\Config $configService, Table\Webhook $webhookTable, Table\Event $eventTable)
    {
        $this->webhookService = $webhookService;
        $this->configService = $configService;
        $this->webhookTable = $webhookTable;
        $this->eventTable = $eventTable;
    }

    public function create(WebhookCreate $webhook, UserContext $context): int
    {
        $this->assertMaxWebhookCount($context);

        // check whether the event exists
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\EventTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\EventTable::COLUMN_NAME, $webhook->getEvent());

        $event = $this->eventTable->findOneBy($condition);
        if (empty($event)) {
            throw new StatusCode\BadRequestException('Event does not exist');
        }

        $backendWebhook = new Model\Backend\WebhookCreate();
        $backendWebhook->setUserId($context->getUserId());
        $backendWebhook->setEventId($event->getId());
        $backendWebhook->setName($webhook->getName());
        $backendWebhook->setEndpoint($webhook->getEndpoint());

        return $this->webhookService->create($backendWebhook, $context);
    }

    public function update(string $webhookId, WebhookUpdate $webhook, UserContext $context): int
    {
        $existing = $this->webhookTable->findOneByIdentifier($context->getTenantId(), $webhookId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find webhook');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Webhook does not belong to the user');
        }

        $backendWebhook = new Model\Backend\WebhookUpdate();
        $backendWebhook->setName($webhook->getName());
        $backendWebhook->setEndpoint($webhook->getEndpoint());

        return $this->webhookService->update((string) $existing->getId(), $backendWebhook, $context);
    }

    public function delete(string $webhookId, UserContext $context): int
    {
        $existing = $this->webhookTable->findOneByIdentifier($context->getTenantId(), $webhookId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find webhook');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Webhook does not belong to the user');
        }

        return $this->webhookService->delete((string) $existing->getId(), $context);
    }

    private function assertMaxWebhookCount(UserContext $context): void
    {
        $count = $this->webhookTable->getCountForUser($context->getTenantId(), $context->getUserId());
        if ($count > $this->configService->getValue('consumer_max_webhooks')) {
            throw new StatusCode\BadRequestException('Maximal amount of tokens reached. Please delete another token in order to generate a new one');
        }
    }
}
