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
use Fusio\Engine\Model\Action;
use Fusio\Engine\Repository;
use Fusio\Impl\Service;
use Fusio\Impl\Table;

/**
 * ActionDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ActionDatabase implements Repository\ActionInterface
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
                       name,
                       class,
                       engine,
                       config,
                       date
                  FROM fusio_action
                 WHERE status = :status
              ORDER BY name ASC';

        $actions = [];
        $result  = $this->connection->fetchAll($sql, [
            'status' => Table\Action::STATUS_ACTIVE
        ]);

        foreach ($result as $row) {
            $actions[] = $this->newAction($row);
        }

        return $actions;
    }

    public function get($actionId)
    {
        if (empty($actionId)) {
            return null;
        }

        if (is_numeric($actionId)) {
            $column = 'id';
        } else {
            $column = 'name';
        }

        $sql = 'SELECT id,
                       name,
                       class,
                       engine,
                       config,
                       date
                  FROM fusio_action
                 WHERE ' . $column . ' = :id';

        $row = $this->connection->fetchAssoc($sql, ['id' => $actionId]);

        if (!empty($row)) {
            return $this->newAction($row);
        } else {
            return null;
        }
    }

    protected function newAction(array $row)
    {
        $config = !empty($row['config']) ? Service\Action::unserializeConfig($row['config']) : [];

        $action = new Action();
        $action->setId($row['id']);
        $action->setName($row['name']);
        $action->setClass($row['class']);
        $action->setEngine($row['engine']);
        $action->setConfig($config);
        $action->setDate($row['date']);

        return $action;
    }
}
