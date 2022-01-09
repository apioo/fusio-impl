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

namespace Fusio\Impl\Table\Event;

use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated;

/**
 * Trigger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Trigger extends Generated\EventTriggerTable
{
    const STATUS_PENDING = 1;
    const STATUS_DONE = 2;

    public function getName(): string
    {
        return 'fusio_event_trigger';
    }

    public function getColumns(): array
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'event_id' => self::TYPE_INT,
            'status' => self::TYPE_INT,
            'payload' => self::TYPE_TEXT,
            'insert_date' => self::TYPE_DATETIME,
        );
    }

    public function getAllPending()
    {
        $sql = 'SELECT id,
                       event_id
                  FROM fusio_event_trigger 
                 WHERE status = :status
              ORDER BY id ASC';

        return $this->connection->fetchAll($sql, [
            'status' => Table\Event\Trigger::STATUS_PENDING
        ]);
    }

    public function markDone($triggerId)
    {
        return $this->connection->update('fusio_event_trigger', [
            'status' => self::STATUS_DONE,
        ], [
            'id' => $triggerId,
        ]);
    }
}
