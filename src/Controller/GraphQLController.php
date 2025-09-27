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

use Fusio\Impl\Service\GraphQL;
use Fusio\Impl\Service\System\FrameworkConfig;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\RequestError;
use JsonException;
use PSX\Api\Attribute\Get;
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
use PSX\Schema\ContentType;
use Throwable;

/**
 * GraphQLController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GraphQLController extends ControllerAbstract implements FilterInterface
{
    public function __construct(
        private readonly GraphQL $server,
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

    #[Get]
    #[Path('/graphql')]
    #[Outgoing(200, ContentType::JSON)]
    public function get(): void
    {
    }

    #[Post]
    #[Path('/graphql')]
    #[Incoming(ContentType::JSON)]
    #[Outgoing(200, ContentType::JSON)]
    public function post(): void
    {
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        if (!$this->frameworkConfig->isGraphQLEnabled()) {
            throw new StatusCode\ServiceUnavailableException('GraphQL service is not enabled');
        }

        $body = (string) $request->getBody();

        try {
            $data = Parser::decode($body, true);
        } catch (JsonException) {
            throw new StatusCode\BadRequestException('Provided an invalid request payload, must be an JSON object or array');
        }

        if (!is_array($data)) {
            throw new StatusCode\BadRequestException('Provided an invalid request payload, must be an JSON object or array');
        }

        try {
            $output = $this->server->run($request->getMethod(), $data, $request->getUri()->getParameters());
        } catch (Throwable $e) {
            $output = new ExecutionResult(null, [Error::createLocatedError($e)]);
        }

        $response->setStatus(200);
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(new StringStream(Parser::encode($output)));

        $filterChain->handle($request, $response);
    }
}
