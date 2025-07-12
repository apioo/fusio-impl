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
use Fusio\Impl\Service\Action\Invoker;
use Fusio\Impl\Service\Form\JsonSchemaResolver;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Mcp\Types\CallToolRequestParams;
use Mcp\Types\CallToolResult;
use Mcp\Types\ListToolsResult;
use Mcp\Types\TextContent;
use Mcp\Types\Tool;
use Mcp\Types\ToolInputSchema;
use PSX\Json\Parser;
use PSX\Record\Record;
use PSX\Schema\Definitions;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\Type\StructDefinitionType;
use PSX\Sql\Condition;

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
        SchemaManagerInterface $schemaManager,
    ) {
        $this->schemaParser = new TypeSchema($schemaManager);
    }

    public function list(): ListToolsResult
    {
        $tools = [];

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_ACTIVE, 1);

        $operations = $this->operationTable->findAll($condition);
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
                $operation->getName(),
                ToolInputSchema::fromArray($inputSchema),
                $operation->getDescription()
            );
        }

        return new ListToolsResult($tools);
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

            $operation = $this->operationTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), null, $params->name);
            if (!$operation instanceof Table\Generated\OperationRow) {
                throw new \RuntimeException('Provided an invalid operation name');
            }

            if ($operation->getHttpMethod() === 'GET') {
                $request = new Request($arguments->getAll(), new Record(), new Request\RpcRequestContext($params->name));
            } else {
                $request = new Request([], $arguments, new Request\RpcRequestContext($params->name));
            }

            $context = new Context();
            $context->setTenantId($this->frameworkConfig->getTenantId());
            $context->setApp(new AppAnonymous());
            $context->setUser(new UserAnonymous());
            $context->setOperation($operation);

            $data = $this->invoker->invoke($request, $context);

            // @TODO use structuredContent
            return new CallToolResult([new TextContent(Parser::encode($data))]);
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

        return (new JsonSchema())->toArray($definitions, 'Parameters');
    }
}
