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

namespace Fusio\Impl\Repository;

use Doctrine\DBAL\Connection as DBALConnection;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Service\Connection as ConnectionService;
use PSX\Framework\Config\ConfigInterface;

/**
 * ConnectionDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ConnectionDatabase implements Repository\ConnectionInterface
{
    private DBALConnection $connection;
    private ConfigInterface $config;

    public function __construct(DBALConnection $connection, ConfigInterface $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    public function getAll(): array
    {
        $sql = 'SELECT id,
                       name, 
                       class
                  FROM fusio_connection 
              ORDER BY name ASC';

        $connections = [];
        $result = $this->connection->fetchAllAssociative($sql);

        foreach ($result as $row) {
            $connections[] = $this->newConnection($row);
        }

        return $connections;
    }

    public function get(string|int $id): ?Model\ConnectionInterface
    {
        if (is_numeric($id)) {
            $column = 'id';
        } else {
            $column = 'name';
        }

        $sql = 'SELECT id,
                       name, 
                       class, 
                       config 
                  FROM fusio_connection 
                 WHERE ' . $column . ' = :id';

        $row = $this->connection->fetchAssociative($sql, ['id' => $id]);

        if (!empty($row)) {
            return $this->newConnection($row);
        } else {
            return null;
        }
    }

    private function newConnection(array $row): Model\ConnectionInterface
    {
        $config = !empty($row['config']) ? ConnectionService\Encrypter::decrypt($row['config'], $this->config->get('fusio_project_key')) : [];

        return new Model\Connection(
            $row['id'],
            $row['name'],
            $row['class'],
            $config
        );
    }
}
