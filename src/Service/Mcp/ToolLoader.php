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

use Fusio\Impl\Service\Agent\ToolName;
use Fusio\Impl\Service\JsonRPC\RPCInvoker;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Mcp\Capability\Registry\ElementReference;
use Mcp\Capability\RegistryInterface;
use Mcp\Schema\Tool;
use Mcp\Schema\ToolAnnotations;
use PSX\Api\Util\Inflection;
use PSX\Json\Parser;
use PSX\Record\Record;
use PSX\Schema\Definitions;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\ObjectMapper;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManager;
use PSX\Schema\Type\Factory\PropertyTypeFactory;
use PSX\Schema\Type\StructDefinitionType;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use stdClass;

/**
 * ToolLoader
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ToolLoader
{
    private TypeSchema $schemaParser;

    public function __construct(
        private RPCInvoker $invoker,
        private Table\Operation $operationTable,
        private FrameworkConfig $frameworkConfig,
        private SchemaManager $schemaManager,
    ) {
        $this->schemaParser = new TypeSchema($schemaManager);
    }

    public function load(RegistryInterface $registry): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_ACTIVE, 1);

        $operations = $this->operationTable->findAll($condition, 0, 1024, Table\Generated\OperationColumn::NAME, OrderBy::ASC);
        foreach ($operations as $operation) {
            $inputSchema = $this->buildSchema($operation);
            if (count($inputSchema) === 0) {
                continue;
            }

            $readOnlyHint = null;
            $destructiveHint = null;
            $idempotentHint = null;
            if ($operation->getHttpMethod() === 'GET') {
                $readOnlyHint = true;
            } elseif ($operation->getHttpMethod() === 'DELETE') {
                $destructiveHint = true;
            }

            if (in_array($operation->getHttpMethod(), ['GET', 'PUT', 'DELETE'], true)) {
                $idempotentHint = true;
            }

            $annotations = new ToolAnnotations($operation->getName(), $readOnlyHint, $destructiveHint, $idempotentHint);

            $tool = new Tool(
                ToolName::toToolName($operation->getName()),
                $inputSchema,
                $operation->getDescription(),
                $annotations,
            );

            $registry->registerTool($tool, function (array $arguments) use ($operation) {
                $arguments = Record::fromArray($arguments);

                $response = $this->invoker->invoke($operation, $arguments);

                return (string) $response->getBody();
            });
        }
    }

    private function buildSchema(Table\Generated\OperationRow $operation): array
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
        if (!$parameters instanceof stdClass) {
            return;
        }

        foreach (get_object_vars($parameters) as $name => $schema) {
            if (!$schema instanceof stdClass) {
                continue;
            }

            $rootType->addProperty($name, $this->schemaParser->parsePropertyType($schema));
        }
    }
}
