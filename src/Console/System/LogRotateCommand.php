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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * LogRotateCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class LogRotateCommand extends Command
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
            ->setName('system:log_rotate')
            ->setDescription('Rotates the log table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaManager = $this->connection->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $tableName = 'fusio_log_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $logTable = $schema->createTable($tableName);
            $logTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logTable->addColumn('route_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('app_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('user_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('ip', 'string', ['length' => 40]);
            $logTable->addColumn('user_agent', 'string', ['length' => 255]);
            $logTable->addColumn('method', 'string', ['length' => 16]);
            $logTable->addColumn('path', 'string', ['length' => 1023]);
            $logTable->addColumn('header', 'text');
            $logTable->addColumn('body', 'text', ['notnull' => false]);
            $logTable->addColumn('execution_time', 'integer', ['notnull' => false, 'default' => null]);
            $logTable->addColumn('date', 'datetime');
            $logTable->setPrimaryKey(['id']);
            $logTable->addOption('engine', 'MyISAM');

            $schemaManager->createTable($logTable);

            $output->writeln('Created log table ' . $tableName);
        }

        // copy all data to archive table
        $result = $this->connection->fetchAll('SELECT route_id, app_id, user_id, ip, user_agent, method, path, header, body, execution_time, date FROM fusio_log');
        foreach ($result as $row) {
            $this->connection->insert($tableName, $row);
        }

        $output->writeln('Copied ' . count($result) . ' entries to archive table');

        // truncate table
        $this->connection->executeUpdate('DELETE FROM fusio_log WHERE 1=1');

        $output->writeln('Truncated log table');
    }
}
