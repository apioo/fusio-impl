<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\AdapterInterface;
use Fusio\Impl\Adapter\Installer;
use Fusio\Impl\Adapter\Instruction;
use Fusio\Impl\Adapter\InstructionParser;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Service;
use PSX\Json\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * RegisterCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RegisterCommand extends Command
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Fusio\Impl\Backend\View\Connection
     */
    protected $connectionView;

    /**
     * @var \Fusio\Impl\Adapter\Installer
     */
    protected $installer;

    /**
     * @var \Fusio\Impl\Adapter\InstructionParser
     */
    protected $parser;

    public function __construct(Service\System\Import $importService, View\Connection $connectionView, Connection $connection)
    {
        parent::__construct();

        $this->connection     = $connection;
        $this->connectionView = $connectionView;
        $this->installer      = new Installer($importService);
        $this->parser         = new InstructionParser();
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

        if (class_exists($class)) {
            $adapter = new $class();
            $helper  = $this->getHelper('question');

            if ($adapter instanceof AdapterInterface) {
                // parse definition
                $definition = file_get_contents($adapter->getDefinition());

                // replace dynamic connection
                if (strpos($definition, '${connection}') !== false) {
                    $output->writeLn('The adapter requires a connection.');

                    $result = $this->connectionView->getCollection();
                    foreach ($result->entry as $connection) {
                        $output->writeln($connection->id . ': ' . $connection->name);
                    }

                    $question = new Question('Please specify the connection (i.e. 1): ', '1');
                    $question->setValidator(function ($answer) {
                        $connection = $this->connectionView->getEntity($answer);

                        return $connection->name;
                    });

                    $name       = $helper->ask($input, $output, $question);
                    $definition = str_replace('${connection}', $name, $definition);
                }

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

                if ($confirmed) {
                    try {
                        $this->connection->beginTransaction();

                        $result = $this->installer->install($instructions);

                        $this->connection->commit();

                        $output->writeln('Registration successful!');
                        $output->writeln('');

                        $logs = $result->getLogs();
                        foreach ($logs as $message) {
                            $output->writeln('- ' . $message);
                        }

                        return 0;
                    } catch (\Throwable $e) {
                        $this->connection->rollback();

                        $output->writeln('An exception occurred during installation of the adapter. No changes are applied to the database.');
                        $output->writeln('');
                        $output->writeln('Message: ' . $e->getMessage());
                        $output->writeln('Trace: ' . $e->getTraceAsString());
                        return 1;
                    }
                } else {
                    $output->writeln('Abort');
                    return 1;
                }
            } else {
                $output->writeln('Class does not implement the AdapterInterface');
                return 1;
            }
        } else {
            $output->writeln('Provided adapter class does not exist');
            return 1;
        }
    }
}
