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
use Fusio\Impl\Service;
use Fusio\Impl\Table;

/**
 * ActionDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActionDatabase implements Repository\ActionInterface
{
    private DBALConnection $connection;
    private bool $async = true;

    public function __construct(DBALConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getAll(): array
    {
        $sql = 'SELECT id,
                       name,
                       class,
                       async,
                       config,
                       date
                  FROM fusio_action
                 WHERE status = :status
              ORDER BY name ASC';

        $actions = [];
        $result  = $this->connection->fetchAllAssociative($sql, [
            'status' => Table\Action::STATUS_ACTIVE
        ]);

        foreach ($result as $row) {
            $actions[] = $this->newAction($row);
        }

        return $actions;
    }

    public function get(string|int $id): ?Model\ActionInterface
    {
        if (empty($id)) {
            return null;
        }

        if (is_numeric($id)) {
            $column = 'id';
        } else {
            $column = 'name';
        }

        $sql = 'SELECT id,
                       name,
                       class,
                       async,
                       config,
                       date
                  FROM fusio_action
                 WHERE ' . $column . ' = :id';

        $row = $this->connection->fetchAssociative($sql, ['id' => $id]);

        if (!empty($row)) {
            return $this->newAction($row);
        } else {
            return null;
        }
    }

    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }

    protected function newAction(array $row): Model\ActionInterface
    {
        $config = !empty($row['config']) ? Service\Action::unserializeConfig($row['config']) : [];

        return new Model\Action(
            $row['id'],
            $row['name'],
            $row['class'],
            $this->async ? (bool) $row['async'] : false,
            $config ?? []
        );
    }
}
