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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Webhook\CreatedEvent;
use Fusio\Impl\Event\Webhook\DeletedEvent;
use Fusio\Impl\Event\Webhook\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\WebhookCreate;
use Fusio\Model\Backend\WebhookUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Webhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Webhook
{
    private Table\Webhook $webhookTable;
    private Webhook\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Webhook $webhookTable, Webhook\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->webhookTable = $webhookTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(WebhookCreate $webhook, UserContext $context): int
    {
        $this->validator->assert($webhook, $context->getTenantId());

        try {
            $this->webhookTable->beginTransaction();

            $row = new Table\Generated\WebhookRow();
            $row->setTenantId($context->getTenantId());
            $row->setEventId($webhook->getEventId());
            $row->setUserId($webhook->getUserId());
            $row->setStatus(Table\Webhook::STATUS_ACTIVE);
            $row->setName($webhook->getName());
            $row->setEndpoint($webhook->getEndpoint());
            $this->webhookTable->create($row);

            $webhookId = $this->webhookTable->getLastInsertId();
            $webhook->setId($webhookId);

            $this->webhookTable->commit();
        } catch (\Throwable $e) {
            $this->webhookTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($webhook, $context));

        return $webhookId;
    }

    public function update(string $webhookId, WebhookUpdate $webhook, UserContext $context): int
    {
        $existing = $this->webhookTable->findOneByIdentifier($context->getTenantId(), $webhookId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find webhook');
        }

        $this->validator->assert($webhook, $context->getTenantId(), $existing);

        try {
            $this->webhookTable->beginTransaction();

            // update webhook
            $existing->setName($webhook->getName() ?? $existing->getName());
            $existing->setEndpoint($webhook->getEndpoint() ?? $existing->getEndpoint());
            $this->webhookTable->update($existing);

            $this->webhookTable->commit();
        } catch (\Throwable $e) {
            $this->webhookTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($webhook, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $webhookId, UserContext $context): int
    {
        $existing = $this->webhookTable->findOneByIdentifier($context->getTenantId(), $webhookId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find webhook');
        }

        try {
            $this->webhookTable->beginTransaction();

            // delete all responses
            $this->webhookTable->deleteAllResponses($existing->getId());

            // remove webhook
            $this->webhookTable->delete($existing);

            $this->webhookTable->commit();
        } catch (\Throwable $e) {
            $this->webhookTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
