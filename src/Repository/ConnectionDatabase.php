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

namespace Fusio\Impl\Repository;

use Doctrine\DBAL\Connection as DBALConnection;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Service\Connection as ConnectionService;

/**
 * ConnectionDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ConnectionDatabase implements Repository\ConnectionInterface
{
    private DBALConnection $connection;
    private string $secretKey;

    public function __construct(DBALConnection $connection, string $secretKey)
    {
        $this->connection = $connection;
        $this->secretKey  = $secretKey;
    }

    public function getAll(): array
    {
        $sql = 'SELECT id,
                       name, 
                       class
                  FROM fusio_connection 
              ORDER BY name ASC';

        $connections = [];
        $result = $this->connection->fetchAll($sql);

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

        $row = $this->connection->fetchAssoc($sql, array('id' => $id));

        if (!empty($row)) {
            return $this->newConnection($row);
        } else {
            return null;
        }
    }

    private function newConnection(array $row): Model\ConnectionInterface
    {
        $config = !empty($row['config']) ? ConnectionService\Encrypter::decrypt($row['config'], $this->secretKey) : [];

        return new Model\Connection(
            $row['id'],
            $row['name'],
            $row['class'],
            $config
        );
    }
}
