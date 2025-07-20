<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Service\Mcp;
use Mcp\Server\ServerRunner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * McpCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class McpCommand extends Command
{
    public function __construct(private Mcp $mcp, private Mcp\ActiveUser $activeUser, private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:mcp')
            ->setAliases(['mcp'])
            ->setDescription('Starts the MCP server')
            ->addArgument('user_id', InputArgument::OPTIONAL, 'Optional a user id under which all actions are executed, by default this is 1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $server = $this->mcp->build();

        $userId = (int) $input->getArgument('user_id');
        if ($userId > 0) {
            $this->activeUser->setUserId($userId);
        }

        $initOptions = $server->createInitializationOptions();

        $runner = new ServerRunner($server, $initOptions, $this->logger);
        $runner->run();

        return self::SUCCESS;
    }
}
