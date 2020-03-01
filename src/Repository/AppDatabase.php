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
use Fusio\Engine\Model\App;
use Fusio\Engine\Repository;
use Fusio\Impl\Table;

/**
 * AppDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AppDatabase implements Repository\AppInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(DBALConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getAll()
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
        $result = $this->connection->fetchAll($sql, [
            'status' => Table\App::STATUS_ACTIVE
        ]);

        foreach ($result as $row) {
            $apps[] = $this->newApp($row);
        }

        return $apps;
    }

    public function get($appId)
    {
        if (empty($appId)) {
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
                 WHERE id = :app_id';

        $row = $this->connection->fetchAssoc($sql, array('app_id' => $appId));

        if (!empty($row)) {
            $app = $this->newApp($row);
            $app->setScopes($this->getScopes($row['id']));

            return $app;
        } else {
            return null;
        }
    }

    protected function getScopes($appId)
    {
        $sql = '    SELECT scope.name
                      FROM fusio_app_scope app_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = app_scope.scope_id
                     WHERE app_scope.app_id = :app_id';

        $result = $this->connection->fetchAll($sql, array('app_id' => $appId)) ?: array();
        $names  = array();

        foreach ($result as $row) {
            $names[] = $row['name'];
        }

        return $names;
    }

    protected function newApp(array $row)
    {
        $parameters = [];
        if (!empty($row['parameters'])) {
            parse_str($row['parameters'], $parameters);
        }

        $app = new App();
        $app->setId($row['id']);
        $app->setUserId($row['user_id']);
        $app->setStatus($row['status']);
        $app->setName($row['name']);
        $app->setUrl($row['url']);
        $app->setParameters($parameters);
        $app->setAppKey($row['app_key']);

        return $app;
    }
}
