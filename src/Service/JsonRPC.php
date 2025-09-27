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

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\Action\Invoker;
use Fusio\Impl\Service\Rate\Limiter;
use Fusio\Impl\Service\Security\TokenValidator;
use Fusio\Impl\Table;
use Fusio\Model;
use InvalidArgumentException;
use PSX\Data\WriterInterface;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\MediaType;
use PSX\Http\Response;
use PSX\Json\Rpc\Context as RpcContext;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use PSX\Record\Record;
use PSX\Schema\ObjectMapper;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;
use stdClass;

/**
 * JsonRPC
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class JsonRPC
{
    public const CONTEXT_AUTHORIZATION = 'authorization';
    public const CONTEXT_IP = 'ip';

    private ObjectMapper $objectMapper;

    public function __construct(
        private Table\Operation $operationTable,
        private Invoker $invoker,
        private ContextFactory $contextFactory,
        private TokenValidator $tokenValidator,
        private Limiter $limiterService,
        private ResponseWriter $responseWriter,
        SchemaManager $schemaManager,
    ) {
        $this->objectMapper = new ObjectMapper($schemaManager);
    }

    public function __invoke(string $method, array|stdClass|null $params, RpcContext $rpcContext): mixed
    {
        if (is_array($params)) {
            throw new InvalidRequestException('Params as array (by-position) are not supported, please use params as object (by-name)');
        }

        if ($params instanceof stdClass) {
            $arguments = Record::fromObject($params);
        } else {
            $arguments = new Record();
        }

        $context = $this->contextFactory->getActive();

        $operation = $this->operationTable->findOneByTenantAndName($context->getTenantId(), null, $method);
        if (!$operation instanceof Table\Generated\OperationRow) {
            throw new \RuntimeException('Provided an invalid operation name');
        }

        $context->setOperation($operation);

        $this->tokenValidator->assertAuthorization($rpcContext->get(self::CONTEXT_AUTHORIZATION), $context);

        $this->limiterService->assertLimit($rpcContext->get(self::CONTEXT_IP), $context->getOperation(), $context->getApp(), $context->getUser());

        $incoming = $operation->getIncoming();
        if (!empty($incoming) && $arguments->containsKey('payload')) {
            $rawPayload = $arguments->get('payload');
            if ($rawPayload instanceof stdClass) {
                $payload = $this->objectMapper->read($rawPayload, SchemaSource::fromString($incoming));
            } else {
                $payload = new Record();
            }

            $arguments->remove('payload');
        } else {
            $payload = new Record();
        }

        $request = new Request($arguments->getAll(), $payload, new Request\RpcRequestContext($method));

        $result = $this->invoker->invoke($request, $context);

        $response = new Response();
        $this->responseWriter->setBody($response, $result, WriterInterface::JSON);

        if ($this->isJson($response)) {
            // in case the response contains JSON data we decode it
            return json_decode((string) $response->getBody());
        } else {
            return $response->getBody();
        }
    }

    private function isJson(Response $response): bool
    {
        try {
            return MediaType\Json::isMediaType(MediaType::parse($response->getHeader('Content-Type')));
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
