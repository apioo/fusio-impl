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

namespace Fusio\Impl\Service\Event;

use Cron\CronExpression;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Cronjob;
use Fusio\Model\Backend\Event;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Event $eventTable;

    public function __construct(Table\Event $eventTable)
    {
        $this->eventTable = $eventTable;
    }

    public function assert(Event $event, ?Table\Generated\EventRow $existing = null): void
    {
        $name = $event->getName();
        if ($name !== null) {
            $this->assertName($name, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Event name must not be empty');
        }
    }

    private function assertName(string $name, ?Table\Generated\EventRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid event name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->eventTable->findOneByName($name)) {
            throw new StatusCode\BadRequestException('Event already exists');
        }
    }
}
