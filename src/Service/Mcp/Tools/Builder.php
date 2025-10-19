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

namespace Fusio\Impl\Service\Mcp\Tools;

use Fusio\Impl\Table;
use Mcp\Types\Tool;
use Mcp\Types\ToolAnnotations;
use Mcp\Types\ToolInputSchema;
use PSX\Api\Util\Inflection;
use PSX\Json\Parser;
use PSX\Schema\Definitions;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManager;
use PSX\Schema\Type\Factory\PropertyTypeFactory;
use PSX\Schema\Type\StructDefinitionType;

/**
 * Builder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Builder
{
    private TypeSchema $schemaParser;

    public function __construct(
        private SchemaManager $schemaManager,
        private Naming $naming,
    ) {
        $this->schemaParser = new TypeSchema($schemaManager);
    }

    public function build(Table\Generated\OperationRow $operation): ?Tool
    {
        $inputSchema = $this->buildSchema($operation);
        if ($inputSchema === null) {
            return null;
        }

        $annotations = new ToolAnnotations();
        if ($operation->getHttpMethod() === 'GET') {
            $annotations->readOnlyHint = true;
        } elseif ($operation->getHttpMethod() === 'DELETE') {
            $annotations->destructiveHint = true;
        }

        if (in_array($operation->getHttpMethod(), ['GET', 'PUT', 'DELETE'], true)) {
            $annotations->idempotentHint = true;
        }

        // @TODO use output schema

        return new Tool(
            $this->naming->toMcpToolName($operation->getName()),
            ToolInputSchema::fromArray($inputSchema),
            $operation->getDescription(),
            $annotations
        );
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
}
