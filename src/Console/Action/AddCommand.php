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

namespace Fusio\Impl\Console\Action;

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
            ->setName('action:add')
            ->setDescription('Adds a new action')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the action')
            ->addArgument('class', InputArgument::REQUIRED, 'The action class i.e. (Acme\Fusio\Action)')
            ->addArgument('engine', InputArgument::OPTIONAL, 'The action engine')
            ->addArgument('config', InputArgument::OPTIONAL, 'Config parameters i.e. foo=bar&bar=foo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->apiExecutor->request('POST', 'action', [
            'name' => $input->getArgument('name'),
            'class' => $input->getArgument('class'),
            'engine' => $input->getArgument('engine'),
            'config' => $this->parseConfig($input->getArgument('config')),
        ]);

        $output->writeln("");
        $output->writeln($response->message);
        $output->writeln("");
    }

    protected function parseConfig($config)
    {
        $data = [];
        parse_str($config, $data);

        return $data;
    }
}
