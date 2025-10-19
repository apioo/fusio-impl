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
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Action\Invoker as ActionInvoker;
use Fusio\Impl\Service\Rate\Limiter;
use Fusio\Impl\Service\Security\TokenValidator;
use Fusio\Impl\Table;
use PSX\Data\WriterInterface;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\Response;
use PSX\Http\ResponseInterface;
use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Schema\ObjectMapper;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;

/**
 * ActiveUser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Invoker
{
    private ObjectMapper $objectMapper;

    public function __construct(
        private ActionInvoker $invoker,
        private TokenValidator $tokenValidator,
        private Limiter $limiterService,
        private ResponseWriter $responseWriter,
        private Table\Operation $operationTable,
        SchemaManager $schemaManager,
    )
    {
        $this->objectMapper = new ObjectMapper($schemaManager);
    }

    public function invoke(string $methodName, RecordInterface $arguments, ?string $authorization, ?string $ip, Context $context): ResponseInterface
    {
        $operation = $this->operationTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), $methodName);
        if (!$operation instanceof Table\Generated\OperationRow) {
            throw new \RuntimeException('Provided an invalid operation name');
        }

        $context->setOperation($operation);

        $this->tokenValidator->assertAuthorization($authorization, $context);

        $this->limiterService->assertLimit($ip, $context->getOperation(), $context->getApp(), $context->getUser());

        $incoming = $operation->getIncoming();
        if (!empty($incoming) && $arguments->containsKey('payload')) {
            $rawPayload = $arguments->get('payload');
            if ($rawPayload instanceof \stdClass) {
                $payload = $this->objectMapper->read($rawPayload, SchemaSource::fromString($incoming));
            } else {
                $payload = new Record();
            }

            $arguments->remove('payload');
        } else {
            $payload = new Record();
        }

        $request = new Request($arguments->getAll(), $payload, new Request\RpcRequestContext($methodName));

        $result = $this->invoker->invoke($request, $context);

        $response = new Response();
        $this->responseWriter->setBody($response, $result, WriterInterface::JSON);

        return $response;
    }
}
