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

use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service\System\Restorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RestoreCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RestoreCommand extends Command
{
    use TypeSafeTrait;

    private Restorer $restorer;

    public function __construct(Restorer $restorer)
    {
        $this->restorer = $restorer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:restore')
            ->setDescription('Restores a deleted database record')
            ->addArgument('type', InputArgument::REQUIRED, 'Type must be one of: ' . implode(', ', $this->restorer->getTypes()))
            ->addArgument('id', InputArgument::REQUIRED, 'Name or id of the record');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $this->getArgumentAsString($input, 'type');
        $id   = $this->getArgumentAsString($input, 'id');

        $result = $this->restorer->restore($type, $id);

        if ($result > 0) {
            $output->writeln('Restored ' . $result . ' record' . ($result > 1 ? 's' : ''));
            return self::SUCCESS;
        } else {
            $output->writeln('Restored no record');
            return self::FAILURE;
        }
    }
}
