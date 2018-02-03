<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Doctrine\DBAL\Connection;
use Fusio\Engine\Model\User;
use Fusio\Engine\Repository;
use Fusio\Impl\Table;

/**
 * UserDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class UserDatabase implements Repository\UserInterface
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getAll()
    {
        $sql = 'SELECT id,
                       status,
                       name
                  FROM fusio_user
                 WHERE status = :status_admin
                    OR status = :status_consumer
              ORDER BY id DESC';

        $users  = [];
        $result = $this->connection->fetchAll($sql, [
            'status_admin'    => Table\User::STATUS_ADMINISTRATOR,
            'status_consumer' => Table\User::STATUS_CONSUMER,
        ]);

        foreach ($result as $row) {
            $users[] = $this->newUser($row);
        }

        return $users;
    }

    public function get($userId)
    {
        if (empty($userId)) {
            return null;
        }

        $sql = 'SELECT id,
                       status,
                       name
                  FROM fusio_user
                 WHERE id = :userId';

        $row = $this->connection->fetchAssoc($sql, array('userId' => $userId));

        if (!empty($row)) {
            return $this->newUser($row);
        } else {
            return null;
        }
    }

    protected function newUser(array $row)
    {
        $user = new User();
        $user->setId($row['id']);
        $user->setStatus($row['status']);
        $user->setName($row['name']);

        return $user;
    }
}
