<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\Model\Connection;
use Fusio\Engine\Repository;
use Fusio\Impl\Service\Connection as ConnectionService;

/**
 * ConnectionDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ConnectionDatabase implements Repository\ConnectionInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param string $secretKey
     */
    public function __construct(DBALConnection $connection, $secretKey)
    {
        $this->connection = $connection;
        $this->secretKey  = $secretKey;
    }

    public function getAll()
    {
        $sql = 'SELECT id,
                       name, 
                       class
                  FROM fusio_connection 
              ORDER BY name ASC';

        $conns  = [];
        $result = $this->connection->fetchAll($sql);

        foreach ($result as $row) {
            $conns[] = $this->newConnection($row);
        }

        return $conns;
    }

    public function get($id)
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

    protected function newConnection(array $row)
    {
        $config = !empty($row['config']) ? ConnectionService\Encrypter::decrypt($row['config'], $this->secretKey) : [];

        $connection = new Connection();
        $connection->setId($row['id']);
        $connection->setName($row['name']);
        $connection->setClass($row['class']);
        $connection->setConfig($config);

        return $connection;
    }
}
