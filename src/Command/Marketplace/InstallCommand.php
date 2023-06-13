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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service;
use Fusio\Model\Backend\MarketplaceInstall;
use PSX\Http\Exception\BadRequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * InstallCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class InstallCommand extends Command
{
    use TypeSafeTrait;

    private Service\Marketplace\Installer $installer;
    private Service\Marketplace\Repository\Remote $remoteRepository;

    public function __construct(Service\Marketplace\Installer $installer, Service\Marketplace\Repository\Remote $remoteRepository)
    {
        parent::__construct();

        $this->installer = $installer;
        $this->remoteRepository = $remoteRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('marketplace:install')
            ->setDescription('Installs an app from the marketplace')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app')
            ->addOption('disable_ssl_verify', 'd', InputOption::VALUE_NONE, 'Disable SSL verification')
            ->addOption('disable_env', 'x', InputOption::VALUE_NONE, 'Disable env replacement');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('disable_ssl_verify')) {
            $this->remoteRepository->setSslVerify(false);
        }

        $replaceEnv = true;
        if ($input->getOption('disable_env')) {
            $replaceEnv = false;
        }

        $name = $this->getArgumentAsString($input, 'name');

        $install = new MarketplaceInstall();
        $install->setName($name);

        try {
            $app = $this->installer->install($install, UserContext::newAnonymousContext(), $replaceEnv);

            $output->writeln('');
            $output->writeln('Installed app ' . $app->getName());
            $output->writeln('');
        } catch (BadRequestException $e) {
            $output->writeln('');
            $output->writeln($e->getMessage());
            $output->writeln('');
        }

        return self::SUCCESS;
    }
}
