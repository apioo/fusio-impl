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

namespace Fusio\Impl\Service\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Table;
use PSX\DateTime\LocalDateTime;
use PSX\Sql\Condition;

/**
 * Dispatcher
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\EventTable::COLUMN_NAME, $eventName);

        $event = $this->eventTable->findOneBy($condition);
        if (empty($event)) {
            throw new \RuntimeException('Invalid event name');
        }

        $row = new Table\Generated\EventTriggerRow();
        $row->setEventId($event->getId());
        $row->setStatus(Table\Event\Trigger::STATUS_PENDING);
        $row->setPayload(json_encode($payload));
        $row->setInsertDate(LocalDateTime::now());
        $this->triggerTable->create($row);
    }
}
