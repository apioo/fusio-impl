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
use Fusio\Impl\Service\System\Deploy;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DeployCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DeployCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\System\Deploy
     */
    protected $deployService;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @param \Fusio\Impl\Service\System\Deploy $deployService
     * @param string $basePath
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Deploy $deployService, $basePath, Connection $connection, LoggerInterface $logger)
    {
        parent::__construct();

        $this->deployService = $deployService;
        $this->basePath      = $basePath;
        $this->connection    = $connection;
        $this->logger        = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('system:deploy')
            ->setAliases(['deploy'])
            ->setDescription('Deploys a Fusio YAML definition')
            ->addArgument('file', InputArgument::OPTIONAL, 'Optional the definition file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (empty($file)) {
            $file = $this->basePath . '/.fusio.yml';
        }

        if (!is_file($file)) {
            throw new \RuntimeException('File does not exists');
        }

        $verbose = $output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL;
        if ($verbose) {
            $this->logger->pushHandler(new StreamHandler(STDOUT));
        } else {
            $this->logger->pushHandler(new NullHandler());
        }

        try {
            $this->connection->beginTransaction();

            $result = $this->deployService->deploy(file_get_contents($file), dirname($file));
            $logs   = $result->getLogs();

            foreach ($logs as $log) {
                $output->writeln('- ' . $log);
            }

            if ($result->hasError()) {
                $errors = $result->getErrors();

                $output->writeln('');
                $output->writeln('Deploy contained ' . count($errors) . ' errors!');
                $output->writeln('');

                foreach ($errors as $error) {
                    $output->writeln('- ' . $error);
                }

                $return = 1;
            } else {
                $output->writeln('');
                $output->writeln('Deploy successful!');
                $output->writeln('');

                $return = 0;
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollback();

            $output->writeln('An exception occurred during deploy. No changes are applied to the database.');
            $output->writeln('');
            $output->writeln('Message: ' . $e->getMessage());
            $output->writeln('Trace: ' . $e->getTraceAsString());

            $return = 1;
        }

        $this->logger->popHandler();

        return $return;
    }
}
