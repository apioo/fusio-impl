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

use Fusio\Engine\AdapterInterface;
use Fusio\Impl\Adapter\Installer;
use Fusio\Impl\Adapter\InstructionParser;
use Fusio\Impl\Console\TypeSafeTrait;
use Fusio\Impl\Provider\ProviderWriter;
use PSX\Json\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * RegisterCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class RegisterCommand extends Command
{
    use TypeSafeTrait;

    private Installer $installer;

    public function __construct(Installer $installer)
    {
        parent::__construct();

        $this->installer = $installer;
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

        $containerFile = $adapter->getContainerFile();
        if (!is_file($containerFile)) {
            $output->writeln('The adapter returned an invalid container file');
            return 1;
        }

        $this->installer->install($containerFile);

        $output->writeln('Registration successful!');
        $output->writeln('');

        return self::SUCCESS;
    }
}
