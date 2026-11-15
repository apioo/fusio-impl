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

use Fusio\Impl\Exception\A2AException;
use Fusio\Impl\Service\Agent\A2A\Server as A2AServer;
use Fusio\Impl\Service\System\FrameworkConfig;
use JsonException;
use PSX\Api\Attribute\Incoming;
use PSX\Api\Attribute\Outgoing;
use PSX\Api\Attribute\Path;
use PSX\Api\Attribute\Post;
use PSX\Framework\Controller\ControllerAbstract;
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
 * A2AController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class A2AController extends ControllerAbstract implements FilterInterface
{
    public function __construct(
        private readonly A2AServer $server,
        private readonly FrameworkConfig $frameworkConfig,
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
    #[Path('/a2a/v1')]
    #[Incoming(ContentType::JSON)]
    #[Outgoing(200, ContentType::JSON)]
    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        if (!$this->frameworkConfig->isA2AEnabled()) {
            throw new StatusCode\ServiceUnavailableException('A2A service is not enabled');
        }

        $this->server->authenticate();

        $body = (string) $request->getBody();

        try {
            $data = Parser::decode($body);
        } catch (JsonException) {
            $data = null;
        }

        $context = new Context();
        $context->put('version', $this->getVersion($request));

        if ($data === null) {
            $return = (object) [
                'jsonrpc' => '2.0',
                'error' => (object) ['code' => -32700, 'message' => 'Invalid JSON payload'],
                'id' => null,
            ];
        } else {
            $return = $this->addErrorDetails(new Server($this->server)->invoke($data, $context));
        }

        $response->setStatus(200);
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(new StringStream(Parser::encode($return)));

        $filterChain->handle($request, $response);
    }

    /**
     * The client sends the protocol version either through the A2A-Version header or the A2A-Version query parameter
     */
    private function getVersion(RequestInterface $request): ?string
    {
        $version = $request->getHeader('A2A-Version');
        if (!empty($version)) {
            return trim($version);
        }

        $version = $request->getUri()->getParameter('A2A-Version');

        return is_string($version) && $version !== '' ? trim($version) : null;
    }

    /**
     * Adds the google.rpc.ErrorInfo detail to all A2A specific errors as described by the specification
     *
     * @param object|array<object> $return
     * @return object|array<object>
     */
    private function addErrorDetails(object|array $return): object|array
    {
        if (is_array($return)) {
            return array_map(fn($response) => $this->addErrorDetails($response), $return);
        }

        $code = $return->error->code ?? null;
        if (!is_int($code)) {
            return $return;
        }

        $reason = A2AException::getReason($code);
        if ($reason !== null) {
            $return->error->data = [
                (object) [
                    '@type' => 'type.googleapis.com/google.rpc.ErrorInfo',
                    'reason' => $reason,
                    'domain' => 'a2a-protocol.org',
                ],
            ];
        }

        return $return;
    }
}
