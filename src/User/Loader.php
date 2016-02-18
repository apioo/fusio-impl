<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\User;

use Doctrine\DBAL\Connection;
use Fusio\Engine\User\LoaderInterface;
use Fusio\Impl\Model\User;

/**
 * Loader
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Loader implements LoaderInterface
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getById($userId)
    {
        $user = $this->newUser($userId);

        if (!empty($userId)) {
            $user->setAnonymous(false);
        } else {
            $user->setAnonymous(true);
        }

        return $user;
    }

    protected function newUser($userId)
    {
        if (empty($userId)) {
            return new User();
        }

        $sql = 'SELECT id,
                       status,
                       name
                  FROM fusio_user
                 WHERE id = :userId';

        $row = $this->connection->fetchAssoc($sql, array('userId' => $userId));

        if (!empty($row)) {
            $user = new User();
            $user->setId($row['id']);
            $user->setStatus($row['status']);
            $user->setName($row['name']);

            return $user;
        } else {
            throw new \RuntimeException('Invalid user id');
        }
    }
}
