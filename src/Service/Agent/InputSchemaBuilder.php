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

use Fusio\Impl\Framework\Schema\Scheme;
use Fusio\Impl\Table\Generated\OperationRow;
use PSX\Api\Util\Inflection;
use PSX\Json\Parser;
use PSX\Schema\Definitions;
use PSX\Schema\Generator\Config;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManager;
use PSX\Schema\Type\Factory\PropertyTypeFactory;
use PSX\Schema\Type\StructDefinitionType;
use stdClass;

/**
 * InputSchemaBuilder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class InputSchemaBuilder
{
    private TypeSchema $schemaParser;

    public function __construct(private SchemaManager $schemaManager)
    {
        $this->schemaParser = new TypeSchema($schemaManager);
    }

    public function build(OperationRow $operation): array
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
            [$scheme] = Scheme::split($incoming);
            if ($scheme === Scheme::MIME) {
                return [];
            }

            $payload = $this->schemaManager->getSchema($incoming);

            $rootType->addProperty('payload', PropertyTypeFactory::getReference($payload->getRoot()));

            $definitions->addSchema('Payload', $payload);
        }

        $definitions->addType('Root', $rootType);

        $config = new Config();
        $config->put('inline_definitions', true);

        return (new JsonSchema($config))->toArray($definitions, 'Root');
    }

    private function buildSchemaFromParameters(OperationRow $operation, StructDefinitionType $rootType): void
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
