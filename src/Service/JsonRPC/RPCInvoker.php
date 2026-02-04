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

namespace Fusio\Impl\Service\JsonRPC;

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\Action\Invoker;
use Fusio\Impl\Service\Rate\Limiter;
use Fusio\Impl\Service\Security\TokenValidator;
use Fusio\Impl\Table\Generated\OperationRow;
use PSX\Data\WriterInterface;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\Response;
use PSX\Json\Parser;
use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Schema\ObjectMapper;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;
use stdClass;

/**
 * RPCInvoker
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class RPCInvoker
{
    private ObjectMapper $objectMapper;

    public function __construct(
        private Invoker $invoker,
        private TokenValidator $tokenValidator,
        private Limiter $limiterService,
        private ResponseWriter $responseWriter,
        private ContextFactory $contextFactory,
        SchemaManager $schemaManager,
    ) {
        $this->objectMapper = new ObjectMapper($schemaManager);
    }

    public function invoke(OperationRow $operation, RecordInterface $arguments): Response
    {
        $context = $this->contextFactory->getActive();
        $context->setOperation($operation);

        $this->tokenValidator->assertAuthorization($context->getAuthorization(), $context);

        $this->limiterService->assertLimit($context->getIp(), $context->getOperation(), $context->getApp(), $context->getUser());

        $incoming = $operation->getIncoming();
        if (!empty($incoming) && $arguments->containsKey('payload')) {
            $rawPayload = $arguments->get('payload');
            if (is_array($rawPayload)) {
                // convert array to stdClass, we need to do this unfortunately since the mcp library uses arrays
                $data = Parser::decode(Parser::encode($rawPayload));

                $payload = $this->objectMapper->read($data, SchemaSource::fromString($incoming));
            } elseif ($rawPayload instanceof stdClass) {
                $payload = $this->objectMapper->read($rawPayload, SchemaSource::fromString($incoming));
            } else {
                $payload = new Record();
            }

            $arguments->remove('payload');
        } else {
            $payload = new Record();
        }

        $request = new Request($arguments->getAll(), $payload, new Request\RpcRequestContext($operation->getName()));

        $result = $this->invoker->invoke($request, $context);

        $response = new Response();
        $this->responseWriter->setBody($response, $result, WriterInterface::JSON);

        return $response;
    }
}
