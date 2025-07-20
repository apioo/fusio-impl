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
use Psr\Log\LoggerInterface;
use PSX\Api\Attribute\Delete;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Incoming;
use PSX\Api\Attribute\Outgoing;
use PSX\Api\Attribute\Path;
use PSX\Api\Attribute\Post;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Exception\ServiceUnavailableException;
use PSX\Http\FilterChainInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Http\Stream\StringStream;
use PSX\Schema\ContentType;

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

    public function getPreFilter(): array
    {
        $filter = parent::getPreFilter();
        $filter[] = function (RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain) {
            $this->run($request, $response);
        };

        return $filter;
    }

    #[Get]
    #[Path('/mcp')]
    #[Outgoing(200, ContentType::JSON)]
    public function get(): void
    {
    }

    #[Post]
    #[Path('/mcp')]
    #[Incoming(ContentType::JSON)]
    #[Outgoing(200, ContentType::JSON)]
    public function post(): void
    {
    }

    #[Delete]
    #[Path('/mcp')]
    #[Outgoing(200, ContentType::JSON)]
    public function delete(): void
    {
    }

    private function run(RequestInterface $request, ResponseInterface $response): void
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
            'resource' => $this->frameworkConfig->getUrl(),
            'authorization_servers' => [$this->frameworkConfig->getDispatchUrl('authorization', 'authorize')],
            'token_validator' => $this->tokenValidator,
        ];

        $runner = new HttpServerRunner($server, $server->createInitializationOptions(), $httpOptions, $this->logger, $this->sessionStore);

        $mcpRequest = $this->toMcpRequest($request);
        $mcpResponse = $runner->handleRequest($mcpRequest);

        $response->setStatus($mcpResponse->getStatusCode());
        $response->setHeaders($mcpResponse->getHeaders());
        $response->setBody(new StringStream((string) $mcpResponse->getBody()));
    }

    private function toMcpRequest(RequestInterface $request): HttpMessage
    {
        $message = new HttpMessage();
        $message->setMethod($request->getMethod());
        $message->setUri($request->getUri()->getPath());
        $message->setQueryParams($request->getUri()->getParameters());

        foreach ($request->getHeaders() as $name => $value) {
            $message->setHeader($name, is_array($value) ? implode(', ', $value) : $value);
        }

        $message->setBody((string) $request->getBody());

        return $message;
    }
}
