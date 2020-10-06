<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Console\App;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\App_Create;
use Fusio\Impl\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AddCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AddCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\App
     */
    protected $appService;

    /**
     * @param \Fusio\Impl\Service\App $appService
     */
    public function __construct(Service\App $appService)
    {
        parent::__construct();

        $this->appService = $appService;
    }

    protected function configure()
    {
        $this
            ->setName('app:add')
            ->setDescription('Adds a new app')
            ->addArgument('userId', InputArgument::REQUIRED, 'The user id of the app')
            ->addArgument('status', InputArgument::REQUIRED, 'The status of the app')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app')
            ->addArgument('url', InputArgument::REQUIRED, 'The url of the app')
            ->addArgument('parameters', InputArgument::REQUIRED, 'Parameters of the app')
            ->addArgument('scopes', InputArgument::REQUIRED, 'Scopes of the app');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $create = new App_Create();
        $create->setUserId((int) $input->getArgument('userId'));
        $create->setStatus((int) $input->getArgument('status'));
        $create->setName($input->getArgument('name'));
        $create->setUrl($input->getArgument('url'));
        $create->setParameters($input->getArgument('parameters'));
        $create->setScopes(explode(',', $input->getArgument('scopes')));

        $this->appService->create($create, UserContext::newAnonymousContext());

        $output->writeln('');
        $output->writeln('App successful created');
        $output->writeln('');

        return 0;
    }
}
