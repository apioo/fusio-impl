<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Event\EventEvents;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Event
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Event
{
    /**
     * @var \Fusio\Impl\Table\Event
     */
    protected $eventTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Event $eventTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Event $eventTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventTable          = $eventTable;
        $this->eventDispatcher     = $eventDispatcher;
    }

    public function create($name, $description, UserContext $context)
    {
        // check whether event exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Event already exists');
        }

        // create event
        $record = [
            'status'      => Table\Event::STATUS_ACTIVE,
            'name'        => $name,
            'description' => $description,
        ];

        $this->eventTable->create($record);

        // get last insert id
        $eventId = $this->eventTable->getLastInsertId();

        $this->eventDispatcher->dispatch(EventEvents::CREATE, new CreatedEvent($eventId, $record, $context));

        return $eventId;
    }

    public function update($eventId, $name, $description, UserContext $context)
    {
        $event = $this->eventTable->get($eventId);

        if (empty($event)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        if ($event['status'] == Table\Event::STATUS_DELETED) {
            throw new StatusCode\GoneException('Event was deleted');
        }

        // update event
        $record = [
            'id'          => $event['id'],
            'name'        => $name,
            'description' => $description,
        ];

        $this->eventTable->update($record);

        $this->eventDispatcher->dispatch(EventEvents::UPDATE, new UpdatedEvent($event['id'], $record, $event, $context));
    }

    public function delete($eventId, UserContext $context)
    {
        $event = $this->eventTable->get($eventId);

        if (empty($event)) {
            throw new StatusCode\NotFoundException('Could not find event');
        }

        $record = [
            'id'     => $event['id'],
            'status' => Table\Rate::STATUS_DELETED,
        ];

        $this->eventTable->update($record);

        $this->eventDispatcher->dispatch(EventEvents::DELETE, new DeletedEvent($event['id'], $event, $context));
    }

    public function exists(string $name)
    {
        $condition  = new Condition();
        $condition->equals('status', Table\Event::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $event = $this->eventTable->getOneBy($condition);

        if (!empty($event)) {
            return $event['id'];
        } else {
            return false;
        }
    }
}
