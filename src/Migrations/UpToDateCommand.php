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

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Tools\Console\Command\UpToDateCommand as DoctrineUpToDateCommand;
use Fusio\Engine\ConnectorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking if your database is up to date or not
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class UpToDateCommand extends DoctrineUpToDateCommand
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Fusio\Engine\ConnectorInterface
     */
    protected $connector;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Fusio\Engine\ConnectorInterface $connector
     */
    public function __construct(Connection $connection, ConnectorInterface $connector)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->connector  = $connector;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('migration:up-to-date')
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'The connection name to use for this command.')
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $outputWriter = new OutputWriter(function($message) use ($output) {
            return $output->writeln($message);
        });

        $connectionId = $input->getOption('connection');
        if (empty($connectionId)) {
            $config = ConfigurationBuilder::fromSystem($this->connection, $outputWriter);
        } else {
            $config = ConfigurationBuilder::fromConnector($connectionId, $this->connector, $outputWriter);
        }

        $this->setMigrationConfiguration($config);

        parent::initialize($input, $output);
    }
}
