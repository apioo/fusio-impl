<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Base;
use Fusio\Impl\Database\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CheckCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CheckCommand extends Command
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
            ->setName('system:check')
            ->setDescription('Status check of the system')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the check i.e. install or user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $check  = $input->getArgument('name');
        $result = null;

        try {
            $result = $this->executeCheck($check);
        } catch (\Throwable $e) {
            $result = false;
        }

        if ($result === false) {
            $output->writeln('Check failed');
            return 1;
        } elseif ($result === true) {
            $output->writeln('Check successful');
            return 0;
        } else {
            $output->writeln('Unknown check');
            return 1;
        }
    }

    protected function executeCheck($check)
    {
        switch ($check) {
            case 'install':
                return $this->checkInstall();
                break;

            case 'upgrade':
                return $this->checkUpgrade();
                break;

            case 'user':
                return $this->checkUser();
                break;
        }

        return null;
    }

    /**
     * Check whether all tables from the schema are available
     *
     * @return bool
     */
    protected function checkInstall()
    {
        $existing = $this->connection->getSchemaManager()->listTableNames();
        $tables   = Installer::getLatestVersion()->getSchema()->getTables();
        $names    = [];
        foreach ($tables as $table) {
            $names[] = $table->getName();
        }

        return count(array_diff($names, $existing)) === 0;
    }

    /**
     * Check whether we need to upgrade
     *
     * @return bool
     */
    protected function checkUpgrade()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('version')
            ->from('fusio_meta')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $version = $this->connection->fetchColumn($qb->getSQL());

        if (!empty($version)) {
            return version_compare($version, Base::getVersion()) === 0;
        }

        return false;
    }

    /**
     * Check whether we have a row in the user table. The installation inserts
     * already a system user so we must have at least more then one user
     *
     * @return bool
     */
    protected function checkUser()
    {
        return $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_user') > 1;
    }
}
