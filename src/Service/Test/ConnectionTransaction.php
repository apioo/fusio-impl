<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Test;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Connector;
use Fusio\Engine\Repository\ConnectionInterface;

/**
 * ConnectionTransaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ConnectionTransaction
{
    private ConnectionInterface $connectionRepository;
    private Connector $connector;
    private Connection $connection;

    public function __construct(ConnectionInterface $connectionRepository, Connector $connector, Connection $connection)
    {
        $this->connectionRepository = $connectionRepository;
        $this->connector = $connector;
        $this->connection = $connection;
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();

        $connections = $this->connectionRepository->getAll();
        foreach ($connections as $connection) {
            $instance = $this->connector->getConnection($connection->getId());
            if ($instance instanceof Connection) {
                $instance->beginTransaction();
            }
        }
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();

        $connections = $this->connectionRepository->getAll();
        foreach ($connections as $connection) {
            $instance = $this->connector->getConnection($connection->getId());
            if ($instance instanceof Connection) {
                $instance->rollBack();
            }
        }
    }
}
