<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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
     * @var \Fusio\Impl\Service\System\ApiExecutor
     */
    protected $apiExecutor;

    /**
     * @param \Fusio\Impl\Service\System\ApiExecutor $apiExecutor
     */
    public function __construct(Service\System\ApiExecutor $apiExecutor)
    {
        parent::__construct();

        $this->apiExecutor = $apiExecutor;
    }

    protected function configure()
    {
        $this
            ->setName('app:add')
            ->setDescription('Adds a new app')
            ->addArgument('userId', InputArgument::REQUIRED, 'The name of the app')
            ->addArgument('status', InputArgument::REQUIRED, 'The name of the app')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app')
            ->addArgument('url', InputArgument::REQUIRED, 'The name of the app')
            ->addArgument('parameters', InputArgument::REQUIRED, 'The name of the app')
            ->addArgument('scopes', InputArgument::REQUIRED, 'The name of the app');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->apiExecutor->request('POST', 'app', [
            'userId' => $input->getArgument('userId'),
            'status' => $input->getArgument('status'),
            'name' => $input->getArgument('name'),
            'url' => $input->getArgument('url'),
            'parameters' => $input->getArgument('parameters'),
            'scopes' => explode(',', $input->getArgument('scopes')),
        ]);

        $output->writeln("");
        $output->writeln($response->message);
        $output->writeln("");
    }
}
