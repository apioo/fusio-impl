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
use Fusio\Engine\Processor;
use Fusio\Impl\Repository\ActionDatabase;
use Fusio\Impl\Table\Generated\ActionQueueTable;

/**
 * Consumer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Consumer
{
    private Processor $processor;
    private Connection $connection;

    public function __construct(Processor $processor, Connection $connection)
    {
        $this->processor  = $processor;
        $this->connection = $connection;
    }

    public function execute()
    {
        $repository = new ActionDatabase($this->connection);
        $repository->setAsync(false);
        $this->processor->push($repository);

        $qb = $this->connection->createQueryBuilder();
        $qb->select([ActionQueueTable::COLUMN_ID, ActionQueueTable::COLUMN_ACTION, ActionQueueTable::COLUMN_REQUEST, ActionQueueTable::COLUMN_CONTEXT]);
        $qb->from(ActionQueueTable::NAME);
        $qb->orderBy(ActionQueueTable::COLUMN_ID, 'DESC');

        $result = $this->connection->fetchAllAssociative($qb->getSQL(), $qb->getParameters());
        foreach ($result as $row) {
            $this->connection->delete(ActionQueueTable::NAME, [
                ActionQueueTable::COLUMN_ID => $row[ActionQueueTable::COLUMN_ID]
            ]);

            $request = Serializer::unserializeRequest($row[ActionQueueTable::COLUMN_REQUEST]);
            $context = Serializer::unserializeContext($row[ActionQueueTable::COLUMN_CONTEXT]);

            try {
                $this->processor->execute($row[ActionQueueTable::COLUMN_ACTION], $request, $context);
            } catch (\Throwable $e) {
                // ignore error and execute next request
                // @TODO maybe log this in the future?
            }
        }
    }
}
