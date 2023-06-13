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

use Fusio\Impl\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CronjobExecuteCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CronjobExecuteCommand extends Command
{
    private Service\Cronjob\Executor $executor;

    public function __construct(Service\Cronjob\Executor $executor)
    {
        parent::__construct();

        $this->executor = $executor;
    }

    protected function configure(): void
    {
        $this
            ->setName('system:cronjob_execute')
            ->setAliases(['cronjob'])
            ->setDescription('Entrypoint to execute cronjobs')
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Whether to execute in daemon mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('daemon')) {
            $this->executor->executeDaemon();
        } else {
            $this->executor->execute();
            $output->writeln('Execution successful');
        }

        return self::SUCCESS;
    }
}
