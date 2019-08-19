<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UpdateCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class UpdateCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\Marketplace\Installer
     */
    protected $installer;

    /**
     * @param \Fusio\Impl\Service\Marketplace\Installer $installer
     */
    public function __construct(Service\Marketplace\Installer $installer)
    {
        parent::__construct();

        $this->installer = $installer;
    }

    protected function configure()
    {
        $this
            ->setName('marketplace:update')
            ->setDescription('Updates an existing locally installed app')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->installer->update($input->getArgument('name'), UserContext::newAnonymousContext());

        $output->writeln('');
        $output->writeln('Updated app ' . $app->getName());
        $output->writeln('');
    }
}
