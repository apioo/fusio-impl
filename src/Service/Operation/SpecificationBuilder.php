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

namespace Fusio\Impl\Service\Operation;

use Fusio\Impl\Framework\Schema\Scheme;
use Fusio\Impl\Table;
use PSX\Api\Operation;
use PSX\Api\Operation\ArgumentInterface;
use PSX\Api\OperationInterface;
use PSX\Api\Specification;
use PSX\Api\SpecificationInterface;
use PSX\Api\Util\Inflection;
use PSX\Schema\ContentType;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Parser\Context\NamespaceContext;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaInterface;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\Type\Factory\PropertyTypeFactory;
use PSX\Schema\Type\ReferencePropertyType;

/**
 * Service which builds a specification based on the schemas defined at the database
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class SpecificationBuilder
{
    private Table\Operation $operationTable;
    private Table\Scope\Operation $scopeTable;
    private SchemaManagerInterface $schemaManager;
    private TypeSchema $schemaParser;

    public function __construct(Table\Operation $operationTable, Table\Scope\Operation $scopeTable, SchemaManagerInterface $schemaManager)
    {
        $this->operationTable = $operationTable;
        $this->scopeTable = $scopeTable;
        $this->schemaManager = $schemaManager;
        $this->schemaParser = new TypeSchema($schemaManager);
    }

    public function build(int $operationId): SpecificationInterface
    {
        $row = $this->operationTable->find($operationId);
        if (!$row instanceof Table\Generated\OperationRow) {
            throw new \RuntimeException('Provided an invalid route');
        }

        $specification = new Specification();
        $scopes = $this->scopeTable->getScopesForOperation($operationId);
        $tags = $this->getTagsFromScopes($scopes);

        $return = $this->buildReturn($row, $specification->getDefinitions());

        $operation = new Operation($row->getHttpMethod(), $row->getHttpPath(), $return);
        $operation->setArguments($this->buildArguments($row, $specification->getDefinitions()));
        $operation->setThrows($this->buildThrows($row, $specification->getDefinitions()));

        if (!empty($row->getDescription())) {
            $operation->setDescription($row->getDescription());
        }

        $operation->setTags($tags);

        if ($row->getStability() === 0) {
            $operation->setStability(OperationInterface::STABILITY_DEPRECATED);
        } elseif ($row->getStability() === 1) {
            $operation->setStability(OperationInterface::STABILITY_EXPERIMENTAL);
        } elseif ($row->getStability() === 2) {
            $operation->setStability(OperationInterface::STABILITY_STABLE);
        } elseif ($row->getStability() === 3) {
            $operation->setStability(OperationInterface::STABILITY_LEGACY);
        }

        if (!$row->getPublic()) {
            $operation->setSecurity($scopes);
            $operation->setAuthorization(true);
        } else {
            $operation->setAuthorization(false);
        }

        $specification->getOperations()->add($row->getName(), $operation);

        return $specification;
    }

    private function buildReturn(Table\Generated\OperationRow $row, DefinitionsInterface $definitions): Operation\Response
    {
        $outgoing = $row->getOutgoing();
        if (empty($outgoing)) {
            throw new \RuntimeException('Provided no outgoing schema');
        }

        return new Operation\Response($row->getHttpCode(), $this->buildSchema($outgoing, $definitions));
    }

    private function buildArguments(Table\Generated\OperationRow $row, DefinitionsInterface $definitions): Operation\Arguments
    {
        $arguments = new Operation\Arguments();

        $this->buildPathParameters($arguments, $row->getHttpPath());

        $incoming = $row->getIncoming();
        if (!empty($incoming)) {
            $arguments->add('payload', new Operation\Argument(ArgumentInterface::IN_BODY, $this->buildSchema($incoming, $definitions)));
        }

        $rawParameters = $row->getParameters();
        if (!empty($rawParameters)) {
            $parameters = \json_decode($rawParameters);
            if ($parameters instanceof \stdClass) {
                $this->buildQueryParametersFromJson($arguments, $parameters);
            }
        }

        return $arguments;
    }

    private function buildThrows(Table\Generated\OperationRow $row, DefinitionsInterface $definitions): array
    {
        $throws = \json_decode($row->getThrows() ?? '');
        if (!$throws instanceof \stdClass) {
            return [];
        }

        $result = [];
        foreach ($throws as $httpCode => $throw) {
            $result[] = new Operation\Response($httpCode, $this->buildSchema($throw, $definitions));
        }

        return $result;
    }

    private function buildPathParameters(Operation\Arguments $arguments, string $path): void
    {
        $names = Inflection::extractPlaceholderNames($path);
        foreach ($names as $name) {
            $arguments->add($name, new Operation\Argument(ArgumentInterface::IN_PATH, PropertyTypeFactory::getString()));
        }
    }

    private function buildQueryParametersFromJson(Operation\Arguments $arguments, \stdClass $parameters): void
    {
        foreach ($parameters as $name => $schema) {
            if (!$schema instanceof \stdClass) {
                continue;
            }

            $arguments->add($name, new Operation\Argument(ArgumentInterface::IN_QUERY, $this->schemaParser->parsePropertyType($schema)));
        }
    }

    private function buildSchema(string $source, DefinitionsInterface $definitions): ContentType|ReferencePropertyType
    {
        [$scheme, $value] = Scheme::split($source);

        if ($scheme === Scheme::MIME) {
            return ContentType::from($value);
        } else {
            $schema = $this->getSchema($source);
            $name = $this->getNameForSchema($source, $schema);

            $definitions->addSchema($name, $schema);

            return PropertyTypeFactory::getReference($name);
        }
    }

    private function getNameForSchema(string $source, SchemaInterface $schema): string
    {
        $root = $schema->getRoot();
        if (!empty($root)) {
            return $root;
        }

        $pos = strpos($source, '://');
        if ($pos === false) {
            return $source;
        }

        return substr($source, $pos + 3);
    }

    /**
     * @return array<string>
     */
    private function getTagsFromScopes(array $scopes): array
    {
        $tags = [];
        foreach ($scopes as $scope) {
            $tagName = $scope;
            if (str_contains($tagName, '.')) {
                $parts = explode('.', $scope);
                $tagName = $parts[array_key_last($parts)] ?? null;
            }
            if (!empty($tagName)) {
                $tags[] = $tagName;
            }
        }

        return $tags;
    }

    private function getSchema(string $schema): SchemaInterface
    {
        $context = null;
        if (str_starts_with($schema, 'php+class://Fusio.Model')) {
            $context = new NamespaceContext(2);
        }

        return $this->schemaManager->getSchema($schema, $context);
    }
}
