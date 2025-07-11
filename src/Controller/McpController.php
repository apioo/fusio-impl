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

namespace Fusio\Impl\Controller;

use Fusio\Impl\Service\Consumer\Mcp;
use Mcp\Server\HttpServerRunner;
use Mcp\Server\Transport\Http\HttpMessage;
use PSX\Api\Attribute\Delete;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Path;
use PSX\Api\Attribute\Post;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\RequestInterface;

/**
 * McpController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class McpController extends ControllerAbstract
{
    public function __construct(private Mcp $mcp, private Mcp\SessionStore $sessionStore)
    {
    }

    #[Get]
    #[Path('/mcp')]
    public function get(RequestInterface $request): HttpResponse
    {
        return $this->run($request);
    }

    #[Post]
    #[Path('/mcp')]
    public function post(RequestInterface $request): HttpResponse
    {
        return $this->run($request);
    }

    #[Delete]
    #[Path('/mcp')]
    public function delete(RequestInterface $request): HttpResponse
    {
        return $this->run($request);
    }

    private function run(RequestInterface $request): HttpResponse
    {
        $server = $this->mcp->build();

        $httpOptions = [
            'session_timeout' => 1800, // 30 minutes
            'max_queue_size' => 500,   // Smaller queue for shared hosting
            'enable_sse' => false,     // No SSE for compatibility
            'shared_hosting' => true,  // Assume shared hosting for max compatibility
            'server_header' => 'Fusio/MCP-PHP-Server/1.0',
        ];

        $runner = new HttpServerRunner($server, $server->createInitializationOptions(), $httpOptions, null, $this->sessionStore);

        $response = $runner->handleRequest(new HttpMessage((string) $request->getBody()));

        return new HttpResponse($response->getStatusCode(), $response->getHeaders(), $response->getBody());
    }
}
