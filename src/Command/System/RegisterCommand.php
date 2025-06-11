<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\AdapterInterface;
use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service\Adapter\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RegisterCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RegisterCommand extends Command
{
    use TypeSafeTrait;

    public function __construct(private Installer $installer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:register')
            ->setDescription('Register an adapter to the system')
            ->addArgument('class', InputArgument::REQUIRED, 'The absolute name of the adapter class (Acme\Fusio\Adapter)')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Confirm automatically all questions with yes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $this->getArgumentAsString($input, 'class');
        if (!class_exists($class)) {
            $output->writeln('Provided adapter class does not exist');
            return 1;
        }

        $adapter = new $class();
        if (!$adapter instanceof AdapterInterface) {
            $output->writeln('Class does not implement the AdapterInterface');
            return 1;
        }

        $this->installer->install($adapter);

        $output->writeln('Registration successful!');
        $output->writeln('');

        return self::SUCCESS;
    }
}
