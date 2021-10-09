<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
    /**
     * @var \Fusio\Impl\Adapter\Installer
     */
    protected $installer;

    /**
     * @var \Fusio\Impl\Adapter\InstructionParser
     */
    protected $parser;

    public function __construct(ProviderWriter $providerWriter)
    {
        parent::__construct();

        $this->installer = new Installer($providerWriter);
        $this->parser    = new InstructionParser();
    }

    protected function configure()
    {
        $this
            ->setName('system:register')
            ->setDescription('Register an adapter to the system')
            ->addArgument('class', InputArgument::REQUIRED, 'The absolute name of the adapter class (Acme\Fusio\Adapter)')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Confirm automatically all questions with yes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');
        if (!class_exists($class)) {
            $output->writeln('Provided adapter class does not exist');
            return 1;
        }

        $adapter = new $class();
        $helper  = $this->getHelper('question');

        if (!$adapter instanceof AdapterInterface) {
            $output->writeln('Class does not implement the AdapterInterface');
            return 1;
        }

        // parse definition
        $definition   = file_get_contents($adapter->getDefinition());
        $definition   = Parser::decode($definition, false);
        $instructions = $this->parser->parse($definition);
        $rows         = array();

        foreach ($instructions as $instruction) {
            $rows[] = [$instruction->getName(), $instruction->getDescription()];
        }

        $output->writeLn('Loaded definition ' . $adapter->getDefinition());

        // confirm
        $autoConfirm = $input->getOption('yes');
        $confirmed   = $autoConfirm;
        if (!$confirmed) {
            // show instructions
            $output->writeLn('');
            $output->writeLn('The adapter will install the following entries into the system.');

            $table = new Table($output);
            $table
                ->setHeaders(['Type', 'Description'])
                ->setRows($rows);

            $table->render();

            $question  = new ConfirmationQuestion('Do you want to continue (y/n)? ', false);
            $confirmed = $helper->ask($input, $output, $question);
        }

        if (!$confirmed) {
            $output->writeln('Abort');
            return 1;
        }

        $this->installer->install($instructions);

        $output->writeln('Registration successful!');
        $output->writeln('');

        return 0;
    }
}
