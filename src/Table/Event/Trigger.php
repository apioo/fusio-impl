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

namespace Fusio\Impl\Table\Event;

use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated;

/**
 * Trigger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Trigger extends Generated\EventTriggerTable
{
    public const STATUS_PENDING = 1;
    public const STATUS_DONE = 2;

    public function getAllPending()
    {
        $sql = 'SELECT id,
                       event_id
                  FROM fusio_event_trigger 
                 WHERE status = :status
              ORDER BY id ASC';

        return $this->connection->fetchAllAssociative($sql, [
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
