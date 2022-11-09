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

namespace Fusio\Impl\Console\System;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CheckCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CheckCommand extends Command
{
    use TypeSafeTrait;

    private Connection $connection;

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
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the check i.e. user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $check = $this->getArgumentAsString($input, 'name');

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

    protected function executeCheck(string $check): ?bool
    {
        switch ($check) {
            case 'user':
                return $this->checkUser();
        }

        return null;
    }

    /**
     * Check whether we have a row in the user table. The installation inserts already a system user so we must have at
     * least more than one user
     */
    protected function checkUser(): bool
    {
        return $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_user') > 1;
    }
}
