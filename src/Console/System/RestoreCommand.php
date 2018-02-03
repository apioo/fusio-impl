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

namespace Fusio\Impl\Console\System;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RestoreCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RestoreCommand extends Command
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function configure()
    {
        $this
            ->setName('system:restore')
            ->setDescription('Restores a deleted database record')
            ->addArgument('type', InputArgument::REQUIRED, 'Type must be one of: action, app, connection, cronjob, routes, schema, user')
            ->addArgument('id', InputArgument::REQUIRED, 'Name or id of the record');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $id   = $input->getArgument('id');

        $result = $this->restore($type, $id);

        if ($result > 0) {
            $output->writeln('Restored ' . $result . ' record' . ($result > 1 ? 's' : ''));
            return 0;
        } else {
            $output->writeln('Restored no record');
            return 1;
        }
    }

    private function restore($type, $id)
    {
        if (is_numeric($id)) {
            $id     = (int) $id;
            $column = 'id';
        } else {
            $column = $type == 'routes' ? 'path' : 'name';
        }

        switch ($type) {
            case 'action':
                return $this->restoreRecord($id, $column, 'fusio_action', Table\Action::STATUS_ACTIVE);
                break;

            case 'app':
                return $this->restoreRecord($id, $column, 'fusio_app', Table\App::STATUS_ACTIVE);
                break;

            case 'connection':
                return $this->restoreRecord($id, $column, 'fusio_connection', Table\Connection::STATUS_ACTIVE);
                break;

            case 'cronjob':
                return $this->restoreRecord($id, $column, 'fusio_cronjob', Table\Cronjob::STATUS_ACTIVE);
                break;

            case 'routes':
                return $this->restoreRecord($id, $column, 'fusio_routes', Table\Routes::STATUS_ACTIVE);
                break;

            case 'schema':
                return $this->restoreRecord($id, $column, 'fusio_schema', Table\Schema::STATUS_ACTIVE);
                break;

            case 'user':
                return $this->restoreRecord($id, $column, 'fusio_user', Table\User::STATUS_DISABLED);
                break;
        }

        return 0;
    }

    private function restoreRecord($id, $column, $table, $status)
    {
        return $this->connection->update($table, [
            'status' => $status,
        ], [
            $column => $id
        ]);
    }
}
