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

use Fusio\Impl\Service\JsonRPC;
use Fusio\Impl\Service\System\FrameworkConfig;
use JsonException;
use PSX\Api\Attribute\Incoming;
use PSX\Api\Attribute\Outgoing;
use PSX\Api\Attribute\Path;
use PSX\Api\Attribute\Post;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Framework\Environment\IPResolver;
use PSX\Http\Exception as StatusCode;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Http\Stream\StringStream;
use PSX\Json\Parser;
use PSX\Json\Rpc\Context;
use PSX\Json\Rpc\Server;
use PSX\Schema\ContentType;

/**
 * JsonRPCController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class JsonRPCController extends ControllerAbstract implements FilterInterface
{
    public function __construct(
        private readonly JsonRPC $server,
        private readonly FrameworkConfig $frameworkConfig,
        private readonly IPResolver $ipResolver,
    ) {
    }

    public function getPreFilter(): array
    {
        $filter = parent::getPreFilter();
        $filter[] = Filter\Tenant::class;
        $filter[] = Filter\Firewall::class;

        return $filter;
    }

    #[Post]
    #[Path('/jsonrpc')]
    #[Incoming(ContentType::JSON)]
    #[Outgoing(200, ContentType::JSON)]
    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        if (!$this->frameworkConfig->isJsonRPCEnabled()) {
            throw new StatusCode\ServiceUnavailableException('JsonRPC service is not enabled');
        }

        $body = (string) $request->getBody();

        try {
            $data = Parser::decode($body);
        } catch (JsonException) {
            throw new StatusCode\BadRequestException('Provided an invalid request payload, must be an JSON object or array');
        }

        $context = new Context();
        $context->put(JsonRPC::CONTEXT_AUTHORIZATION, $request->getHeader('Authorization'));
        $context->put(JsonRPC::CONTEXT_IP, $this->ipResolver->resolveByRequest($request));

        $return = (new Server($this->server))->invoke($data, $context);

        $response->setStatus(200);
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(new StringStream(Parser::encode($return)));

        $filterChain->handle($request, $response);
    }
}
