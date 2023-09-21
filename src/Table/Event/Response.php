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
 * Response
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Response extends Generated\EventResponseTable
{
    public const STATUS_PENDING = 1;
    public const STATUS_DONE = 2;
    public const STATUS_EXCEEDED = 3;

    public function getAllBySubscription(int $subscriptionId): array
    {
        $sql = 'SELECT response.id,
                       response.status,
                       response.code,
                       response.attempts,
                       response.error,
                       response.execute_date
                  FROM fusio_event_response response
                 WHERE response.subscription_id = :id
              ORDER BY response.execute_date DESC, response.id ASC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 8);

        return $this->connection->fetchAllAssociative($sql, [
            'id' => $subscriptionId
        ]);
    }
}
