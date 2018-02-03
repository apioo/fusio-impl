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
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Base;
use Fusio\Impl\Database\Installer;
use Fusio\Impl\Database\Preview;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * InstallCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class InstallCommand extends Command
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
            ->setName('system:install')
            ->setAliases(['install'])
            ->setDescription('Installs or upgrades the database to the latest schema')
            ->addOption('preview', 'p', InputOption::VALUE_NONE, 'Shows all SQL queries which are executed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fromSchema = $this->connection->getSchemaManager()->createSchema();
        $preview    = $input->getOption('preview');

        if ($preview) {
            $installer = new Preview($this->connection, function($query) use ($output){
                $output->writeln($query);
            });
        } else {
            $installer = new Installer($this->connection);
        }

        // execute install or upgrade
        $currentVersion = $this->getInstalledVersion($fromSchema);

        if ($currentVersion !== null) {
            if (!$preview) {
                $output->writeln('Upgrade from version ' . $currentVersion . ' to ' . Base::getVersion());
            }

            $installer->upgrade($currentVersion, Base::getVersion());

            if (!$preview) {
                $output->writeln('Upgrade successful');
            }
        } else {
            if (!$preview) {
                $output->writeln('Install version ' . Base::getVersion());
            }

            $installer->install(Base::getVersion());

            if (!$preview) {
                $output->writeln('Installation successful');
            }
        }
    }

    protected function getInstalledVersion(Schema $schema)
    {
        if ($schema->hasTable('fusio_meta')) {
            $version = $this->connection->fetchColumn('SELECT version FROM fusio_meta ORDER BY installDate DESC, id DESC LIMIT 1');
            if (!empty($version)) {
                return $version;
            }
        }

        return null;
    }
}
