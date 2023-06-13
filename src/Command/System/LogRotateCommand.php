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

use Fusio\Impl\Service\System\LogRotator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * LogRotateCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LogRotateCommand extends Command
{
    private LogRotator $logRotator;

    public function __construct(LogRotator $logRotator)
    {
        parent::__construct();

        $this->logRotator = $logRotator;
    }

    protected function configure(): void
    {
        $this
            ->setName('system:log_rotate')
            ->setDescription('Rotates the log table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->logRotator->rotate() as $message) {
            $output->writeln($message);
        }

        return self::SUCCESS;
    }
}
