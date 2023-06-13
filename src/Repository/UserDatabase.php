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

use Doctrine\DBAL\Connection;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Table;

/**
 * UserDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
                       role_id,
                       plan_id,
                       status,
                       external_id,
                       name,
                       email,
                       points
                  FROM fusio_user
                 WHERE status = :status
              ORDER BY id DESC';

        $users  = [];
        $result = $this->connection->fetchAllAssociative($sql, [
            'status' => Table\User::STATUS_ACTIVE,
        ]);

        foreach ($result as $row) {
            $users[] = $this->newUser($row);
        }

        return $users;
    }

    public function get(string|int $id): ?Model\UserInterface
    {
        if (empty($id)) {
            return null;
        }

        $sql = 'SELECT id,
                       role_id,
                       plan_id,
                       status,
                       external_id,
                       name,
                       email,
                       points
                  FROM fusio_user
                 WHERE id = :id';

        $row = $this->connection->fetchAssociative($sql, ['id' => $id]);

        if (!empty($row)) {
            return $this->newUser($row);
        } else {
            return null;
        }
    }

    private function newUser(array $row): Model\UserInterface
    {
        return new Model\User(
            false,
            $row['id'],
            $row['role_id'],
            $this->getCategoryForRole($row['role_id']),
            $row['status'],
            $row['name'],
            $row['email'] ?? '',
            $row['points'] ?? 0,
            $row['external_id'] ?? null,
            $row['plan_id'] ?? null
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
