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
use Fusio\Impl\Service\Form\JsonSchemaResolver;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Mcp\Types\CallToolRequestParams;
use Mcp\Types\CallToolResult;
use Mcp\Types\ListToolsResult;
use Mcp\Types\PaginatedRequestParams;
use Mcp\Types\TextContent;
use Mcp\Types\Tool;
use Mcp\Types\ToolInputSchema;
use PSX\Data\WriterInterface;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\Response;
use PSX\Json\Parser;
use PSX\Record\Record;
use PSX\Schema\Definitions;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManagerInterface;
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

    public function __construct(
        private Table\Operation $operationTable,
        private JsonSchemaResolver $jsonSchemaResolver,
        private Invoker $invoker,
        private FrameworkConfig $frameworkConfig,
        private ActiveUser $activeUser,
        private UserDatabase $userRepository,
        private ResponseWriter $responseWriter,
        SchemaManagerInterface $schemaManager,
    ) {
        $this->schemaParser = new TypeSchema($schemaManager);
    }

    public function list(PaginatedRequestParams $params): ListToolsResult
    {
        $cursor = $params->cursor ?? null;

        $userId = $this->activeUser->getUserId();
        if (!empty($userId)) {
            $user = $this->userRepository->get($userId) ?? throw new \RuntimeException('Provided an invalid active user');
        } else {
            $user = new UserAnonymous();
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        if ($user->getCategoryId() > 0) {
            $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $user->getCategoryId());
        }
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_ACTIVE, 1);

        $count = 128;
        $startIndex = empty($cursor) ? 0 : ((int) base64_decode($cursor));
        $nextCursor = base64_encode('' . ($startIndex + $count));

        $tools = [];
        $operations = $this->operationTable->findAll($condition, $startIndex, $count, Table\Generated\OperationColumn::NAME, OrderBy::ASC);
        foreach ($operations as $operation) {
            if ($operation->getHttpMethod() === 'GET') {
                $inputSchema = $this->buildSchemaFromParameters($operation);
            } else {
                $inputSchema = $this->jsonSchemaResolver->resolveIncoming($operation);
            }

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
            } else {
                $user = new UserAnonymous();
            }

            $categoryId = null;
            if ($user->getCategoryId() > 0) {
                $categoryId = $user->getCategoryId();
            }

            $operation = $this->operationTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), $categoryId, $this->toOperationId($params->name));
            if (!$operation instanceof Table\Generated\OperationRow) {
                throw new \RuntimeException('Provided an invalid operation name');
            }

            if ($operation->getHttpMethod() === 'GET') {
                $request = new Request($arguments->getAll(), new Record(), new Request\RpcRequestContext($params->name));
            } else {
                $request = new Request($arguments->getAll(), $arguments, new Request\RpcRequestContext($params->name));
            }

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

    private function buildSchemaFromParameters(Table\Generated\OperationRow $operation): ?array
    {
        $rawParameters = $operation->getParameters();
        if (empty($rawParameters)) {
            return null;
        }

        $parameters = Parser::decode($rawParameters);
        if (!$parameters instanceof \stdClass) {
            return null;
        }

        $type = new StructDefinitionType();
        foreach ($parameters as $name => $schema) {
            if (!$schema instanceof \stdClass) {
                continue;
            }

            $type->addProperty($name, $this->schemaParser->parsePropertyType($schema));
        }

        $definitions = new Definitions();
        $definitions->addType('Parameters', $type);

        return (new JsonSchema(inlineDefinitions: true))->toArray($definitions, 'Parameters');
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
