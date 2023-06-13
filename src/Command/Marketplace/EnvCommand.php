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

namespace Fusio\Impl\Command\Marketplace;

use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service;
use PSX\Http\Exception\BadRequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EnvCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EnvCommand extends Command
{
    use TypeSafeTrait;

    private Service\Marketplace\Installer $installer;

    public function __construct(Service\Marketplace\Installer $installer)
    {
        parent::__construct();

        $this->installer = $installer;
    }

    protected function configure(): void
    {
        $this
            ->setName('marketplace:env')
            ->setDescription('Replaces env variables of an existing app')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $this->getArgumentAsString($input, 'name');

        try {
            $app = $this->installer->env($name);

            $output->writeln('');
            $output->writeln('Replaced env ' . $app->getName());
            $output->writeln('');
        } catch (BadRequestException $e) {
            $output->writeln('');
            $output->writeln($e->getMessage());
            $output->writeln('');
        }

        return self::SUCCESS;
    }
}
