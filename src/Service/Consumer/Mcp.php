<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service\Consumer;

use Fusio\Engine\ContextInterface;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Request;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Consumer\Mcp\Tools;
use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Json\Rpc\Server;

/**
 * Mcp
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Mcp
{
    public function __construct(private FrameworkConfig $frameworkConfig, private ProcessorInterface $processor, private Tools $tools)
    {
    }

    public function run(RequestInterface $request, ContextInterface $context): mixed
    {
        $callback = function (string $method, mixed $payload) use ($context) {
            if ($method === 'tools/list') {
                return $this->tools->list($context);
            } elseif ($method === 'initialize') {
            } elseif ($method === 'ping') {
                return (object) [];
            } elseif ($method === 'notifications/initialized') {
            } elseif ($method === 'notifications/cancelled') {
            } else {
                return $this->execute($method, $payload, $context);
            }
        };

        $server = new Server($callback, $this->frameworkConfig->isDebug());

        return $server->invoke($request->getPayload());
    }

    private function execute(string $actionId, mixed $payload, ContextInterface $context): mixed
    {
        $actionId = 'action://' . $actionId;

        $arguments = $payload;

        $request = new Request($arguments, $payload, new Request\RpcRequestContext($actionId));

        return $this->processor->execute($actionId, $request, $context);
    }

    private function listTools(): mixed
    {

    }
}
