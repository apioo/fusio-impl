<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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
use Fusio\Impl\Event\Trigger\CreatedEvent;
use Fusio\Impl\Event\Trigger\DeletedEvent;
use Fusio\Impl\Event\Trigger\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\TriggerCreate;
use Fusio\Model\Backend\TriggerUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Trigger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Trigger
{
    public function __construct(
        private Table\Trigger $triggerTable,
        private Trigger\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(TriggerCreate $trigger, UserContext $context): int
    {
        $this->validator->assert($trigger, $context->getTenantId());

        try {
            $this->triggerTable->beginTransaction();

            $row = new Table\Generated\TriggerRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Cronjob::STATUS_ACTIVE);
            $row->setName($trigger->getName() ?? throw new StatusCode\BadRequestException('No name provided'));
            $row->setEvent($trigger->getEvent() ?? throw new StatusCode\BadRequestException('No event provided'));
            $row->setAction($trigger->getAction() ?? throw new StatusCode\BadRequestException('No action provided'));
            $row->setMetadata($trigger->getMetadata() !== null ? Parser::encode($trigger->getMetadata()) : null);
            $this->triggerTable->create($row);

            $triggerId = $this->triggerTable->getLastInsertId();
            $trigger->setId($triggerId);

            $this->triggerTable->commit();
        } catch (\Throwable $e) {
            $this->triggerTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($trigger, $context));

        return $triggerId;
    }

    public function update(string $triggerId, TriggerUpdate $trigger, UserContext $context): int
    {
        $existing = $this->triggerTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $triggerId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $this->validator->assert($trigger, $context->getTenantId(), $existing);

        $existing->setName($trigger->getName() ?? $existing->getName());
        $existing->setEvent($trigger->getEvent() ?? $existing->getEvent());
        $existing->setAction($trigger->getAction() ?? $existing->getAction());
        $existing->setMetadata($trigger->getMetadata() !== null ? Parser::encode($trigger->getMetadata()) : $existing->getMetadata());
        $this->triggerTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($trigger, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $cronjobId, UserContext $context): int
    {
        $existing = $this->triggerTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $existing->setStatus(Table\Cronjob::STATUS_DELETED);
        $this->triggerTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
