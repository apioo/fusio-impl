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

namespace Fusio\Impl\Service\Action\Queue;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Processor;
use Fusio\Impl\Repository\ActionDatabase;
use Fusio\Impl\Table\Generated\ActionQueueRow;
use Fusio\Impl\Table\Generated\ActionQueueTable;

/**
 * Consumer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
