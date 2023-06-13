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

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Fusio\Impl\Worker\ClientFactory;
use PSX\Framework\Config\ConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

/**
 * WaitForCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class WaitForCommand extends Command
{
    private const MAX_TRY = 40;

    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    protected function configure(): void
    {
        $this
            ->setName('system:wait_for')
            ->setDescription('Command which waits until all required external connections are available');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->config->get('psx_connection');
        if (is_string($connection)) {
            $params = (new DsnParser())->parse($connection);
        } elseif (is_array($connection)) {
            $params = $connection;
        } else {
            throw new \RuntimeException('Invalid connection');
        }

        $this->waitFor('database', $output, function() use ($params) {
            $connection = DriverManager::getConnection($params);
            $connection->fetchFirstColumn($connection->getDatabasePlatform()->getDummySelectSQL());
        });

        $worker = $this->config->get('fusio_worker');
        if (!empty($worker) && is_array($worker)) {
            foreach ($worker as $type => $endpoint) {
                $this->waitFor($type, $output, function() use ($endpoint, $type) {
                    ClientFactory::getClient($endpoint, $type);
                });
            }
        }

        return self::SUCCESS;
    }

    private function waitFor(string $name, OutputInterface $output, \Closure $closure): void
    {
        $count = 0;
        while ($count < self::MAX_TRY) {
            try {
                $closure();
                $output->writeln('* Connection to ' . $name . ' successful');
                return;
            } catch (\Throwable $e) {
            }

            $output->writeln('* Waiting for connection ' . $name);
            sleep(3);
            $count++;
        }

        throw new \RuntimeException('Could not connect to ' . $name);
    }
}
