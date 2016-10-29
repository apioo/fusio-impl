<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Backend\Filter\Routes\Path as PathFilter;
use Fusio\Impl\Service;
use PSX\Json\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
     * @var \Fusio\Impl\Service\Connection
     */
    protected $connectionService;

    /**
     * @var \Fusio\Impl\Adapter\Installer
     */
    protected $installer;

    /**
     * @var \Fusio\Impl\Adapter\InstructionParser
     */
    protected $parser;

    public function __construct(Service\System\Import $importService, Service\Connection $connectionService, Connection $connection)
    {
        parent::__construct();

        $this->connection        = $connection;
        $this->connectionService = $connectionService;
        $this->installer         = new Installer($importService);
        $this->parser            = new InstructionParser();
    }

    protected function configure()
    {
        $this
            ->setName('system:register')
            ->setDescription('Register an adapter to the system')
            ->addArgument('class', InputArgument::REQUIRED, 'The absolute name of the adapter class (Acme\Fusio\Adapter)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');

        if (class_exists($class)) {
            $adapter = new $class();
            $helper  = $this->getHelper('question');

            if ($adapter instanceof AdapterInterface) {
                // parse definition
                $definition   = Parser::decode(file_get_contents($adapter->getDefinition()), false);
                $instructions = $this->parser->parse($definition);
                $rows         = array();
                $hasRoutes    = false;
                $hasDatabase  = false;

                foreach ($instructions as $instruction) {
                    $rows[] = [$instruction->getName(), $instruction->getDescription()];

                    if ($instruction instanceof Instruction\Route) {
                        $hasRoutes = true;
                    } elseif ($instruction instanceof Instruction\Database) {
                        $hasDatabase = true;
                    }
                }

                // show instructions
                $output->writeLn('Loaded definition ' . $adapter->getDefinition());
                $output->writeLn('');
                $output->writeLn('The adapter will install the following entries into the system.');

                $table = $this->getHelper('table');
                $table
                    ->setHeaders(['Type', 'Description'])
                    ->setRows($rows);

                $table->render($output);

                // confirm
                $question = new ConfirmationQuestion('Do you want to continue (y/n)? ', false);

                if ($helper->ask($input, $output, $question)) {
                    // if the adapter installs new routes ask for a base path
                    if ($hasRoutes) {
                        $output->writeLn('');
                        $output->writeLn('The adapter inserts new routes into the system.');
                        $output->writeLn('Please specify a base path under which the new routes are inserted.');

                        $filter   = new PathFilter();
                        $question = new Question('Base path (i.e. /acme/service): ', '/');
                        $question->setValidator(function ($answer) use ($filter) {

                            if (!$filter->apply($answer)) {
                                throw new \RuntimeException(sprintf($filter->getErrorMessage(), 'Base path'));
                            }

                            return $answer;

                        });

                        $basePath = $helper->ask($input, $output, $question);
                    } else {
                        $basePath = null;
                    }

                    // if the adapter installs new tables ask for the connection
                    if ($hasDatabase) {
                        $output->writeLn('');
                        $output->writeLn('The adapter creates a new table into the system.');
                        $output->writeLn('Please select the connection id which should be used.');

                        $connections = $this->connectionService->getAll();
                        foreach ($connections->entry as $connection) {
                            $output->writeLn($connection->id . ': ' . $connection->name);
                        }

                        $question = new Question('Connection id (i.e. 1): ', 1);
                        $question->setValidator(function ($answer) {

                            $connection = $this->connectionService->get($answer);
                            if (empty($connection)) {
                                throw new \RuntimeException('Invalid connection id');
                            }

                            return $connection->id;

                        });

                        $connectionId = $helper->ask($input, $output, $question);
                    } else {
                        $connectionId = null;
                    }

                    try {
                        $this->connection->beginTransaction();

                        $this->installer->install($instructions, $basePath, $connectionId);

                        $this->connection->commit();

                        $output->writeln('Registration successful');
                    } catch (\Exception $e) {
                        $this->connection->rollback();

                        $output->writeln('An exception occured during installation of the adapter. No changes are applied to the database.');
                        $output->writeln('');
                        $output->writeln('Message: ' . $e->getMessage());
                        $output->writeln('Trace: ' . $e->getTraceAsString());
                    }
                } else {
                    $output->writeln('Abort');
                }
            } else {
                $output->writeln('Class does not implement the AdapterInterface');
            }
        } else {
            $output->writeln('Provided adapter class does not exist');
        }
    }
}
