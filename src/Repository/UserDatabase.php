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
 * @link    https://www.fusio-project.org
 */
class UserDatabase implements Repository\UserInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getAll(): array
    {
        $sql = 'SELECT id,
                       status,
                       name
                  FROM fusio_user
                 WHERE status = :status
              ORDER BY id DESC';

        $users  = [];
        $result = $this->connection->fetchAll($sql, [
            'status' => Table\User::STATUS_ACTIVE,
        ]);

        foreach ($result as $row) {
            $users[] = $this->newUser($row);
        }

        return $users;
    }

    public function get(string|int $id): ?User
    {
        if (empty($id)) {
            return null;
        }

        $sql = 'SELECT id,
                       role_id,
                       status,
                       name,
                       email,
                       points
                  FROM fusio_user
                 WHERE id = :id';

        $row = $this->connection->fetchAssoc($sql, array('id' => $id));

        if (!empty($row)) {
            return $this->newUser($row);
        } else {
            return null;
        }
    }

    private function newUser(array $row)
    {
        return new User(
            false,
            $row['id'],
            $row['role_id'],
            $this->getCategoryForRole($row['role_id']),
            $row['status'],
            $row['name'],
            $row['email'],
            $row['points'] ?? 0,
        );
    }

    private function getCategoryForRole($roleId): int
    {
        $categoryId = $this->connection->fetchOne('SELECT category_id FROM fusio_role WHERE id = :id', ['id' => $roleId]);
        if (empty($categoryId)) {
            return 0;
        }

        return (int) $categoryId;
    }
}
