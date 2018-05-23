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

namespace Fusio\Impl\Service\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * Dispatcher
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * @var \Fusio\Impl\Table\Event
     */
    protected $eventTable;

    /**
     * @var \Fusio\Impl\Table\Event\Trigger
     */
    protected $triggerTable;

    /**
     * @param \Fusio\Impl\Table\Event $eventTable
     * @param \Fusio\Impl\Table\Event\Trigger $triggerTable
     */
    public function __construct(Table\Event $eventTable, Table\Event\Trigger $triggerTable)
    {
        $this->eventTable   = $eventTable;
        $this->triggerTable = $triggerTable;
    }

    /**
     * @inheritdoc
     */
    public function dispatch($eventName, $payload)
    {
        // check whether event exists
        $condition  = new Condition();
        $condition->equals('name', $eventName);

        $event = $this->eventTable->getOneBy($condition);

        if (empty($event)) {
            throw new \RuntimeException('Invalid event name');
        }

        $this->triggerTable->create([
            'eventId' => $event->id,
            'status' => Table\Event\Trigger::STATUS_PENDING,
            'payload' => json_encode($payload),
            'insertDate' => new \DateTime(),
        ]);
    }
}
