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

namespace Fusio\Impl\Command\System;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Command\TypeSafeTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CheckCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

    protected function configure(): void
    {
        $this
            ->setName('system:check')
            ->setDescription('Status check of the system')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the check i.e. user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $check = $this->getArgumentAsString($input, 'name');

        try {
            $result = $this->executeCheck($check);
        } catch (\Throwable $e) {
            $result = false;
        }

        if ($result === false) {
            $output->writeln('Check failed');
            return self::FAILURE;
        } elseif ($result === true) {
            $output->writeln('Check successful');
            return self::SUCCESS;
        } else {
            $output->writeln('Unknown check');
            return self::FAILURE;
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
        return $this->connection->fetchOne('SELECT COUNT(*) FROM fusio_user') > 1;
    }
}
