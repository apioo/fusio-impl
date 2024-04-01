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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Event\CreatedEvent;
use Fusio\Impl\Event\Event\DeletedEvent;
use Fusio\Impl\Event\Event\UpdatedEvent;
use Fusio\Impl\Framework\Schema\Scheme;
use Fusio\Impl\Table;
use Fusio\Model\Backend\EventCreate;
use Fusio\Model\Backend\EventUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Event
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Event
{
    private Table\Event $eventTable;
    private Event\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Event $eventTable, Event\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventTable = $eventTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(EventCreate $event, UserContext $context): int
    {
        $this->validator->assert($event, $context->getTenantId());

        // create event
        try {
            $this->eventTable->beginTransaction();

            $row = new Table\Generated\EventRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Event::STATUS_ACTIVE);
            $row->setName($event->getName());
            $row->setDescription($event->getDescription());
            $row->setEventSchema(Scheme::wrap($event->getSchema()));
            $row->setMetadata($event->getMetadata() !== null ? json_encode($event->getMetadata()) : null);
            $this->eventTable->create($row);

            $eventId = $this->eventTable->getLastInsertId();
            $event->setId($eventId);

            $this->eventTable->commit();
        } catch (\Throwable $e) {
            $this->eventTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($event, $context));

        return $eventId;
    }

    public function update(string $eventId, EventUpdate $event, UserContext $context): int
    {
        $existing = $this->eventTable->findOneByIdentifier($context->getTenantId(), $eventId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        if ($existing->getStatus() == Table\Event::STATUS_DELETED) {
            throw new StatusCode\GoneException('Event was deleted');
        }

        $this->validator->assert($event, $context->getTenantId(), $existing);

        // update event
        $existing->setName($event->getName() ?? $existing->getName());
        $existing->setDescription($event->getDescription() ?? $existing->getDescription());
        $existing->setEventSchema($event->getSchema() ?? $existing->getEventSchema());
        $existing->setMetadata($event->getMetadata() !== null ? json_encode($event->getMetadata()) : $existing->getMetadata());
        $this->eventTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($event, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $eventId, UserContext $context): int
    {
        $existing = $this->eventTable->findOneByIdentifier($context->getTenantId(), $eventId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        if ($existing->getStatus() == Table\Event::STATUS_DELETED) {
            throw new StatusCode\GoneException('Event was deleted');
        }

        $existing->setStatus(Table\Rate::STATUS_DELETED);
        $this->eventTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
