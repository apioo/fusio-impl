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

namespace Fusio\Impl\Command\System;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Fusio\Impl\Worker\ClientFactory;
use PSX\Framework\Config\Config;
use PSX\Framework\Config\ConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * WaitForCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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

    protected function configure()
    {
        $this
            ->setName('system:wait_for')
            ->setDescription('Command which waits until all required external connections are available');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $params = $this->config->get('psx_connection');
        $config = new Configuration();

        $this->waitFor('database', $output, function() use ($params, $config) {
            $connection = DriverManager::getConnection($params, $config);
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

        return 0;
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
