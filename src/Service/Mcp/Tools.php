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

namespace Fusio\Impl\Service\Mcp;

use Fusio\Engine\Model\AppAnonymous;
use Fusio\Engine\Model\UserAnonymous;
use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Repository\UserDatabase;
use Fusio\Impl\Service\Action\Invoker;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Mcp\Types\CallToolRequestParams;
use Mcp\Types\CallToolResult;
use Mcp\Types\ListToolsResult;
use Mcp\Types\PaginatedRequestParams;
use Mcp\Types\TextContent;
use Mcp\Types\Tool;
use Mcp\Types\ToolInputSchema;
use PSX\Api\Util\Inflection;
use PSX\Data\WriterInterface;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\Response;
use PSX\Json\Parser;
use PSX\Record\Record;
use PSX\Schema\Definitions;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\ObjectMapper;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;
use PSX\Schema\Type\Factory\PropertyTypeFactory;
use PSX\Schema\Type\StructDefinitionType;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Tools
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Tools
{
    private TypeSchema $schemaParser;
    private ObjectMapper $objectMapper;

    public function __construct(
        private Table\Operation $operationTable,
        private Invoker $invoker,
        private FrameworkConfig $frameworkConfig,
        private ActiveUser $activeUser,
        private UserDatabase $userRepository,
        private ResponseWriter $responseWriter,
        private SchemaManager $schemaManager,
    ) {
        $this->schemaParser = new TypeSchema($schemaManager);
        $this->objectMapper = new ObjectMapper($schemaManager);
    }

    public function list(PaginatedRequestParams $params): ListToolsResult
    {
        $cursor = $params->cursor ?? null;

        $userId = $this->activeUser->getUserId();
        if (!empty($userId)) {
            $user = $this->userRepository->get($userId) ?? throw new \RuntimeException('Provided an invalid active user');
            $categoryId = $user->getCategoryId();
        } else {
            $categoryId = 0;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        if ($categoryId > 0) {
            $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $categoryId);
        }
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_ACTIVE, 1);

        $count = 32;
        $startIndex = empty($cursor) ? 0 : ((int) base64_decode($cursor));
        $nextCursor = base64_encode('' . ($startIndex + $count));

        $tools = [];
        $operations = $this->operationTable->findAll($condition, $startIndex, $count, Table\Generated\OperationColumn::ID, OrderBy::DESC);
        foreach ($operations as $operation) {
            $inputSchema = $this->buildSchema($operation);
            if ($inputSchema === null) {
                continue;
            }

            // @TODO use output schema
            //$outputSchema = $this->jsonSchemaResolver->resolveOutgoing($operation);

            $tools[] = new Tool(
                $this->toMcpToolName($operation->getName()),
                ToolInputSchema::fromArray($inputSchema),
                $operation->getDescription()
            );
        }

        if (count($tools) === 0) {
            $nextCursor = null;
        }

        return new ListToolsResult($tools, $nextCursor);
    }

    public function call(CallToolRequestParams $params): CallToolResult
    {
        try {
            $rawArguments = $params->arguments;
            if (is_array($rawArguments)) {
                $arguments = Record::from($rawArguments);
            } else {
                $arguments = new Record();
            }

            $userId = $this->activeUser->getUserId();
            if (!empty($userId)) {
                $user = $this->userRepository->get($userId) ?? throw new \RuntimeException('Provided an invalid active user');
                $categoryId = $user->getCategoryId();
            } else {
                $user = $this->userRepository->get(1);
                $categoryId = null;
            }

            $operation = $this->operationTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), $categoryId, $this->toOperationId($params->name));
            if (!$operation instanceof Table\Generated\OperationRow) {
                throw new \RuntimeException('Provided an invalid operation name');
            }

            $incoming = $operation->getIncoming();
            if (!empty($incoming) && $arguments->containsKey('payload')) {
                $rawPayload = $arguments->get('payload');
                if (is_array($rawPayload)) {
                    // convert array to stdClass, we need to do this unfortunately since the mcp library uses arrays
                    $data = Parser::decode(Parser::encode($rawPayload));

                    $payload = $this->objectMapper->read($data, SchemaSource::fromString($incoming));
                } elseif ($rawPayload instanceof \stdClass) {
                    $payload = $this->objectMapper->read($rawPayload, SchemaSource::fromString($incoming));
                } else {
                    $payload = new Record();
                }

                $arguments->remove('payload');
            } else {
                $payload = new Record();
            }

            $request = new Request($arguments->getAll(), $payload, new Request\RpcRequestContext($params->name));

            $context = new Context();
            $context->setTenantId($this->frameworkConfig->getTenantId());
            $context->setApp(new AppAnonymous());
            $context->setUser($user);
            $context->setOperation($operation);

            $result = $this->invoker->invoke($request, $context);

            $response = new Response();
            $this->responseWriter->setBody($response, $result, WriterInterface::JSON);
            $text = (string) $response->getBody();

            return new CallToolResult([new TextContent($text)]);
        } catch (\Throwable $e) {
            return new CallToolResult([new TextContent('Failed to execute ' . $params->name . ': ' . $e->getMessage())], isError: true);
        }
    }

    private function buildSchema(Table\Generated\OperationRow $operation): ?array
    {
        $rootType = new StructDefinitionType();
        $definitions = new Definitions();

        $names = Inflection::extractPlaceholderNames($operation->getHttpPath());
        foreach ($names as $name) {
            $rootType->addProperty($name, PropertyTypeFactory::getString());
        }

        $this->buildSchemaFromParameters($operation, $rootType);

        $incoming = $operation->getIncoming();
        if (!empty($incoming)) {
            $payload = $this->schemaManager->getSchema($incoming);

            $rootType->addProperty('payload', PropertyTypeFactory::getReference($payload->getRoot()));

            $definitions->addSchema('Payload', $payload);
        }

        $definitions->addType('Root', $rootType);

        return (new JsonSchema(inlineDefinitions: true))->toArray($definitions, 'Root');
    }

    private function buildSchemaFromParameters(Table\Generated\OperationRow $operation, StructDefinitionType $rootType): void
    {
        $rawParameters = $operation->getParameters();
        if (empty($rawParameters)) {
            return;
        }

        $parameters = Parser::decode($rawParameters);
        if (!$parameters instanceof \stdClass) {
            return;
        }

        foreach ($parameters as $name => $schema) {
            if (!$schema instanceof \stdClass) {
                continue;
            }

            $rootType->addProperty($name, $this->schemaParser->parsePropertyType($schema));
        }
    }

    private function toMcpToolName(string $name): string
    {
        return str_replace('.', '-', $name);
    }

    private function toOperationId(string $name): string
    {
        return str_replace('-', '.', $name);
    }
}
