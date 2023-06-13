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
use Fusio\Impl\Table;

/**
 * AppDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AppDatabase implements Repository\AppInterface
{
    private DBALConnection $connection;

    public function __construct(DBALConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getAll(): array
    {
        $sql = 'SELECT id,
                       user_id,
                       status,
                       name,
                       url,
                       parameters,
                       app_key
                  FROM fusio_app
                 WHERE status = :status
              ORDER BY id DESC';

        $apps   = [];
        $result = $this->connection->fetchAllAssociative($sql, [
            'status' => Table\App::STATUS_ACTIVE
        ]);

        foreach ($result as $row) {
            $apps[] = $this->newApp($row, []);
        }

        return $apps;
    }

    public function get(string|int $id): ?Model\AppInterface
    {
        if (empty($id)) {
            return null;
        }

        $sql = 'SELECT id,
                       user_id,
                       status,
                       name,
                       url,
                       parameters,
                       app_key
                  FROM fusio_app
                 WHERE id = :id';

        $row = $this->connection->fetchAssociative($sql, array('id' => $id));

        if (!empty($row)) {
            return $this->newApp($row, $this->getScopes($row['id']));
        } else {
            return null;
        }
    }

    protected function getScopes(string|int $appId): array
    {
        $sql = '    SELECT scope.name
                      FROM fusio_app_scope app_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = app_scope.scope_id
                     WHERE app_scope.app_id = :app_id';

        $result = $this->connection->fetchAllAssociative($sql, array('app_id' => $appId)) ?: array();
        $names  = array();

        foreach ($result as $row) {
            $names[] = $row['name'];
        }

        return $names;
    }

    protected function newApp(array $row, array $scopes): Model\AppInterface
    {
        $parameters = [];
        if (!empty($row['parameters'])) {
            parse_str($row['parameters'], $parameters);
        }

        return new Model\App(
            false,
            $row['id'],
            $row['user_id'],
            $row['status'],
            $row['name'],
            $row['url'],
            $row['app_key'],
            $parameters,
            $scopes
        );
    }
}
