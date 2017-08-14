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
use Fusio\Impl\Adapter\Transform;
use Fusio\Impl\Service;
use Monolog\Handler\NullHandler;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ImportCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ImportCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\System\Import
     */
    protected $importService;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(Service\System\Import $importService, Connection $connection, LoggerInterface $logger)
    {
        parent::__construct();

        $this->importService = $importService;
        $this->connection    = $connection;
        $this->logger        = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('system:import')
            ->setDescription('Import system data from a JSON structure')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the JSON file')
            ->addArgument('format', InputArgument::OPTIONAL, 'Optional the format i.e. openapi, raml, swagger');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (!is_file($file)) {
            throw new RuntimeException('File does not exists');
        }

        $verbose = $output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL;
        if (!$verbose) {
            $this->logger->pushHandler(new NullHandler());
        }

        try {
            $format = $input->getArgument('format');
            $schema = file_get_contents($file);

            if (!empty($format)) {
                $import = Transform::fromSchema($format, $schema);
            } else {
                $import = file_get_contents($file);
            }

            $this->connection->beginTransaction();

            $result = $this->importService->import($import);

            $this->connection->commit();

            $output->writeln('Import successful!');
            $output->writeln('The following actions were done:');
            $output->writeln('');

            foreach ($result as $message) {
                $output->writeln('- ' . $message);
            }

            $return = 0;
        } catch (\Exception $e) {
            $this->connection->rollback();

            $output->writeln('An exception occurred during import. No changes are applied to the database.');
            $output->writeln('');
            $output->writeln('Message: ' . $e->getMessage());
            $output->writeln('Trace: ' . $e->getTraceAsString());

            $return = 1;
        }

        if (!$verbose) {
            $this->logger->popHandler();
        }

        return $return;
    }
}
