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

namespace Fusio\Impl\Console\Connection;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Action_Config;
use Fusio\Impl\Backend\Model\Connection_Config;
use Fusio\Impl\Backend\Model\Connection_Create;
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
     * @var \Fusio\Impl\Service\Connection
     */
    protected $connectionService;

    /**
     * @param \Fusio\Impl\Service\Connection $connectionService
     */
    public function __construct(Service\Connection $connectionService)
    {
        parent::__construct();

        $this->connectionService = $connectionService;
    }

    protected function configure()
    {
        $this
            ->setName('connection:add')
            ->setDescription('Adds a new connection')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the connection')
            ->addArgument('class', InputArgument::REQUIRED, 'The absolute name of the connection class (Acme\Fusio\Connection)')
            ->addArgument('config', InputArgument::OPTIONAL, 'Config parameters i.e. foo=bar&bar=foo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $create = new Connection_Create();
        $create->setName($input->getArgument('name'));
        $create->setClass($input->getArgument('class'));
        $create->setConfig($this->parseConfig($input->getArgument('config')));

        $this->connectionService->create($create, UserContext::newAnonymousContext());

        $output->writeln('');
        $output->writeln('Connection successful created');
        $output->writeln('');

        return 0;
    }

    protected function parseConfig(?string $raw): ?Connection_Config
    {
        if (empty($raw)) {
            return null;
        }

        $config = new Connection_Config();
        $data = [];
        parse_str($raw, $data);

        foreach ($data as $key => $value) {
            $config->setProperty($key, $value);
        }

        return $config;
    }
}
