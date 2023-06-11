<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Event\CreatedEvent;
use Fusio\Impl\Event\Event\DeletedEvent;
use Fusio\Impl\Event\Event\UpdatedEvent;
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
 * @license http://www.gnu.org/licenses/agpl-3.0
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

    public function create(int $categoryId, EventCreate $event, UserContext $context): int
    {
        $this->validator->assert($event);

        // create event
        try {
            $this->eventTable->beginTransaction();

            $row = new Table\Generated\EventRow();
            $row->setCategoryId($categoryId);
            $row->setStatus(Table\Event::STATUS_ACTIVE);
            $row->setName($event->getName());
            $row->setDescription($event->getDescription());
            $row->setEventSchema($event->getSchema());
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
        $existing = $this->eventTable->findOneByIdentifier($eventId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        if ($existing->getStatus() == Table\Event::STATUS_DELETED) {
            throw new StatusCode\GoneException('Event was deleted');
        }

        $this->validator->assert($event, $existing);

        // update event
        $existing->setName($event->getName() ?? $existing->getName());
        $existing->setDescription($event->getDescription() ?? $existing->getDescription());
        $existing->setEventSchema($event->getSchema() ?? $existing->getEventSchema());
        $existing->setMetadata($event->getMetadata() !== null ? json_encode($event->getMetadata()) : $existing->getMetadata());
        $this->eventTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($event, $existing, $context));

        return $existing->getId();
    }

    public function delete(int $eventId, UserContext $context): int
    {
        $existing = $this->eventTable->findOneByIdentifier($eventId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        $existing->setStatus(Table\Rate::STATUS_DELETED);
        $this->eventTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
