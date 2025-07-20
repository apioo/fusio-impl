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

namespace Fusio\Impl\Service;

use Mcp\Server\Server;
use Mcp\Types\CallToolRequestParams;
use Mcp\Types\GetPromptRequestParams;
use Mcp\Types\PaginatedRequestParams;
use Mcp\Types\ReadResourceRequestParams;
use Psr\Log\LoggerInterface;

/**
 * Mcp
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Mcp
{
    public function __construct(private Config $configService, private Mcp\Prompts $prompts, private Mcp\Resources $resources, private Mcp\Tools $tools, private LoggerInterface $logger)
    {
    }

    public function build(): Server
    {
        $server = new Server($this->configService->getValue('info_title'), $this->logger);

        $server->registerHandler('prompts/list', function(PaginatedRequestParams $params) {
            return $this->prompts->list($params);
        });

        $server->registerHandler('prompts/get', function(GetPromptRequestParams $params) {
            return $this->prompts->get($params);
        });

        $server->registerHandler('resources/list', function(PaginatedRequestParams $params) {
            return $this->resources->list($params);
        });

        $server->registerHandler('resources/read', function(ReadResourceRequestParams $params) {
            return $this->resources->get($params);
        });

        $server->registerHandler('tools/list', function(PaginatedRequestParams $params) {
            return $this->tools->list($params);
        });

        $server->registerHandler('tools/call', function(CallToolRequestParams $params) {
            return $this->tools->call($params);
        });


        return $server;
    }
}
