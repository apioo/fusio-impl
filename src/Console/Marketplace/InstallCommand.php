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

namespace Fusio\Impl\Console\Marketplace;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Model\Backend\Marketplace_Install;
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
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InstallCommand extends Command
{
    private Service\Marketplace\Installer $installer;
    private Service\Marketplace\Repository\Remote $remoteRepository;

    public function __construct(Service\Marketplace\Installer $installer, Service\Marketplace\Repository\Remote $remoteRepository)
    {
        parent::__construct();

        $this->installer = $installer;
        $this->remoteRepository = $remoteRepository;
    }

    protected function configure()
    {
        $this
            ->setName('marketplace:install')
            ->setDescription('Installs an app from the marketplace')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app')
            ->addOption('disable_ssl_verify', 'd', InputOption::VALUE_NONE, 'Disable SSL verification')
            ->addOption('disable_env', 'x', InputOption::VALUE_NONE, 'Disable env replacement');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('disable_ssl_verify')) {
            $this->remoteRepository->setSslVerify(false);
        }

        $replaceEnv = true;
        if ($input->getOption('disable_env')) {
            $replaceEnv = false;
        }

        $install = new Marketplace_Install();
        $install->setName($input->getArgument('name'));

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

        return 0;
    }
}
