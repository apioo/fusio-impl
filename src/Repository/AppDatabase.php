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
use Fusio\Impl\Table;

/**
 * AppDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
