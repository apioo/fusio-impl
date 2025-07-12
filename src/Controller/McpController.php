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

use Fusio\Impl\Base;
use Fusio\Impl\Service\Mcp;
use Fusio\Impl\Service\System\FrameworkConfig;
use Mcp\Server\HttpServerRunner;
use Mcp\Server\Transport\Http\HttpMessage;
use Mcp\Shared\RequestResponder;
use Psr\Log\LoggerInterface;
use PSX\Api\Attribute\Delete;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Path;
use PSX\Api\Attribute\Post;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception\ServiceUnavailableException;
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
    public function __construct(
        private readonly Mcp $mcp,
        private readonly Mcp\SessionStore $sessionStore,
        private readonly Mcp\TokenValidator $tokenValidator,
        private readonly LoggerInterface $logger,
        private readonly FrameworkConfig $frameworkConfig,
    ) {
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
        if (!$this->frameworkConfig->isMCPEnabled()) {
            throw new ServiceUnavailableException('MCP service is not enabled');
        }

        $server = $this->mcp->build();

        $httpOptions = [
            'session_timeout' => $this->frameworkConfig->getMCPSessionTimeout(),
            'enable_sse' => false,
            'max_queue_size' => $this->frameworkConfig->getMCPQueueSize(),
            'server_header' => 'Fusio/' . Base::getVersion(),
            'auth_enabled' => true,
            'token_validator' => $this->tokenValidator,
        ];

        $runner = new HttpServerRunner($server, $server->createInitializationOptions(), $httpOptions, $this->logger, $this->sessionStore);

        $response = $runner->handleRequest($this->toHttpMessage($request));

        return new HttpResponse($response->getStatusCode(), $response->getHeaders(), $response->getBody());
    }

    private function toHttpMessage(RequestInterface $request): HttpMessage
    {
        $message = new HttpMessage();
        $message->setMethod($request->getMethod());
        $message->setUri($request->getUri()->getPath());
        $message->setQueryParams($request->getUri()->getParameters());

        foreach ($request->getHeaders() as $name => $value) {
            $message->setHeader($name, $value);
        }

        $message->setBody((string) $request->getBody());

        return $message;
    }
}
