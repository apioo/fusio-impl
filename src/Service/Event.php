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
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Event $eventTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventTable      = $eventTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, EventCreate $event, UserContext $context): int
    {
        $name = $event->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        // check whether event exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Event already exists');
        }

        // create event
        try {
            $this->eventTable->beginTransaction();

            $record = new Table\Generated\EventRow([
                Table\Generated\EventTable::COLUMN_CATEGORY_ID => $categoryId,
                Table\Generated\EventTable::COLUMN_STATUS => Table\Event::STATUS_ACTIVE,
                Table\Generated\EventTable::COLUMN_NAME => $event->getName(),
                Table\Generated\EventTable::COLUMN_DESCRIPTION => $event->getDescription(),
                Table\Generated\EventTable::COLUMN_EVENT_SCHEMA => $event->getSchema(),
                Table\Generated\EventTable::COLUMN_METADATA => $event->getMetadata() !== null ? json_encode($event->getMetadata()) : null,
            ]);

            $this->eventTable->create($record);

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

    public function update(int $eventId, EventUpdate $event, UserContext $context): int
    {
        $existing = $this->eventTable->find($eventId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        if ($existing->getStatus() == Table\Event::STATUS_DELETED) {
            throw new StatusCode\GoneException('Event was deleted');
        }

        // update event
        $record = new Table\Generated\EventRow([
            Table\Generated\EventTable::COLUMN_ID => $existing->getId(),
            Table\Generated\EventTable::COLUMN_NAME => $event->getName(),
            Table\Generated\EventTable::COLUMN_DESCRIPTION => $event->getDescription(),
            Table\Generated\EventTable::COLUMN_EVENT_SCHEMA => $event->getSchema(),
            Table\Generated\EventTable::COLUMN_METADATA => $event->getMetadata() !== null ? json_encode($event->getMetadata()) : null,
        ]);

        $this->eventTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($event, $existing, $context));

        return $eventId;
    }

    public function delete(int $eventId, UserContext $context): int
    {
        $existing = $this->eventTable->find($eventId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        $record = new Table\Generated\EventRow([
            Table\Generated\EventTable::COLUMN_ID => $existing->getId(),
            Table\Generated\EventTable::COLUMN_STATUS => Table\Rate::STATUS_DELETED,
        ]);

        $this->eventTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $eventId;
    }

    public function exists(string $name): int|false
    {
        $condition  = new Condition();
        $condition->equals(Table\Generated\EventTable::COLUMN_STATUS, Table\Event::STATUS_ACTIVE);
        $condition->equals(Table\Generated\EventTable::COLUMN_NAME, $name);

        $event = $this->eventTable->findOneBy($condition);

        if ($event instanceof Table\Generated\EventRow) {
            return $event->getId();
        } else {
            return false;
        }
    }
}
