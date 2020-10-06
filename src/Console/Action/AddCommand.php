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

namespace Fusio\Impl\Console\Action;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Action_Config;
use Fusio\Impl\Backend\Model\Action_Create;
use Fusio\Impl\Factory\EngineDetector;
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
     * @var \Fusio\Impl\Service\Action
     */
    protected $actionService;

    /**
     * @param \Fusio\Impl\Service\Action $actionService
     */
    public function __construct(Service\Action $actionService)
    {
        parent::__construct();

        $this->actionService = $actionService;
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
        $class  = $input->getArgument('class');
        $engine = $input->getArgument('engine');

        if (empty($engine)) {
            $engine = EngineDetector::getEngine($class);
        }

        $create = new Action_Create();
        $create->setName($input->getArgument('name'));
        $create->setClass($class);
        $create->setEngine($engine);
        $create->setConfig($this->parseConfig($input->getArgument('config')));

        $this->actionService->create($create, UserContext::newAnonymousContext());

        $output->writeln('');
        $output->writeln('Action successful created');
        $output->writeln('');

        return 0;
    }

    protected function parseConfig(?string $raw): ?Action_Config
    {
        if (empty($raw)) {
            return null;
        }

        $config = new Action_Config();
        $data = [];
        parse_str($raw, $data);

        foreach ($data as $key => $value) {
            $config->setProperty($key, $value);
        }

        return $config;
    }
}
