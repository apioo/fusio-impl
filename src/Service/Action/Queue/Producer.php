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

namespace Fusio\Impl\Service\Action\Queue;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Action\QueueInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Table\Generated\ActionQueueTable;

/**
 * Producer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Producer implements QueueInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function push(string|int $actionId, RequestInterface $request, ContextInterface $context): void
    {
        $this->connection->insert(ActionQueueTable::NAME, [
            ActionQueueTable::COLUMN_ACTION => $actionId,
            ActionQueueTable::COLUMN_REQUEST => Serializer::serializeRequest($request),
            ActionQueueTable::COLUMN_CONTEXT => Serializer::serializeContext($context),
            ActionQueueTable::COLUMN_DATE => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }
}
