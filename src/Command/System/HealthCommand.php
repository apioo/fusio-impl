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

use Fusio\Impl\Service\System\Health;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * HealthCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class HealthCommand extends Command
{
    public function __construct(private Health $health)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:health')
            ->setDescription('Checks whether the system is healthy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->health->check();
        foreach ($result->getChecks() as $name => $check) {
            $healthy = $check['healthy'] ? '✓' : '✖';

            $output->writeln('[' . $healthy . '] ' . $name);

            if (isset($check['error'])) {
                $output->writeln('    ' . $check['error']);
            }
        }

        return $result->isHealthy() ? self::SUCCESS : self::FAILURE;
    }
}
