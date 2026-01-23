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

namespace Fusio\Impl\Service\Agent;

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\Action\Invoker;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Data\WriterInterface;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\Response;
use PSX\Json\Parser;
use PSX\Record\Record;
use PSX\Schema\ObjectMapper;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;
use RuntimeException;
use stdClass;
use Symfony\AI\Platform\Result\ToolCall;

/**
 * OperationTool
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class OperationTool
{
    private ObjectMapper $objectMapper;

    public function __construct(
        private Table\Operation $operationTable,
        private Invoker $invoker,
        private FrameworkConfig $frameworkConfig,
        private ResponseWriter $responseWriter,
        private ContextFactory $contextFactory,
        SchemaManager $schemaManager,
    ) {
        $this->objectMapper = new ObjectMapper($schemaManager);
    }

    public function invoke(ToolCall $toolCall): mixed
    {
        $arguments = Record::fromArray($toolCall->getArguments());
        $context = $this->contextFactory->getActive();

        $operation = $this->operationTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), null, ToolName::toOperationId($toolCall->getName()));
        if (!$operation instanceof Table\Generated\OperationRow) {
            throw new RuntimeException('Provided an invalid operation name');
        }

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

        $request = new Request($arguments->getAll(), $payload, new Request\RpcRequestContext($toolCall->getName()));

        $result = $this->invoker->invoke($request, $context);

        $response = new Response();
        $this->responseWriter->setBody($response, $result, WriterInterface::JSON);

        return (string) $response->getBody();
    }
}
