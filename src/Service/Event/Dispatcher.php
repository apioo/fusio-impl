<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @link    https://www.fusio-project.org
 */
class Dispatcher implements DispatcherInterface
{
    private Table\Event $eventTable;
    private Table\Event\Trigger $triggerTable;

    public function __construct(Table\Event $eventTable, Table\Event\Trigger $triggerTable)
    {
        $this->eventTable   = $eventTable;
        $this->triggerTable = $triggerTable;
    }

    public function dispatch(string $eventName, mixed $payload): void
    {
        // check whether event exists
        $condition  = new Condition();
        $condition->equals(Table\Generated\EventTable::COLUMN_NAME, $eventName);

        $event = $this->eventTable->findOneBy($condition);
        if (empty($event)) {
            throw new \RuntimeException('Invalid event name');
        }

        $record = new Table\Generated\EventTriggerRow([
            Table\Generated\EventTriggerTable::COLUMN_EVENT_ID => $event->getId(),
            Table\Generated\EventTriggerTable::COLUMN_STATUS => Table\Event\Trigger::STATUS_PENDING,
            Table\Generated\EventTriggerTable::COLUMN_PAYLOAD => json_encode($payload),
            Table\Generated\EventTriggerTable::COLUMN_INSERT_DATE => new \DateTime(),
        ]);

        $this->triggerTable->create($record);
    }
}
