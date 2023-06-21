<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table;
use PSX\Api\Operation;
use PSX\Api\Operation\ArgumentInterface;
use PSX\Api\Specification;
use PSX\Api\SpecificationInterface;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaInterface;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\Type\ReferenceType;
use PSX\Schema\TypeFactory;

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

        $return = $this->getReturn($row, $specification->getDefinitions());

        $operation = new Operation($row->getHttpMethod(), $row->getHttpPath(), $return);
        $operation->setArguments($this->getArguments($row, $specification->getDefinitions()));
        $operation->setThrows($this->getThrows($row, $specification->getDefinitions()));

        if (!empty($row->getDescription())) {
            $operation->setDescription($row->getDescription());
        }

        $operation->setTags($tags);

        if (!$row->getPublic()) {
            $operation->setSecurity($scopes);
        }

        $specification->getOperations()->add($row->getName(), $operation);

        return $specification;
    }

    private function getReturn(Table\Generated\OperationRow $row, DefinitionsInterface $definitions): Operation\Response
    {
        $outgoing = $row->getOutgoing();
        if (empty($outgoing)) {
            throw new \RuntimeException('Provided no outgoing schema');
        }

        $schema = $this->schemaManager->getSchema($outgoing);
        $name = $this->getNameForSchema($outgoing, $schema);

        $definitions->addSchema($name, $schema);

        return new Operation\Response($row->getHttpCode(), TypeFactory::getReference($name));
    }

    private function getArguments(Table\Generated\OperationRow $row, DefinitionsInterface $definitions): Operation\Arguments
    {
        $arguments = new Operation\Arguments();

        $this->buildPathParameters($arguments, $row->getHttpPath());

        $incoming = $row->getIncoming();
        if (!empty($incoming)) {
            $schema = $this->schemaManager->getSchema($incoming);
            $name = $this->getNameForSchema($incoming, $schema);

            $definitions->addSchema($name, $schema);

            $arguments->add('payload', new Operation\Argument(ArgumentInterface::IN_BODY, TypeFactory::getReference($name)));
        }

        $parameters = \json_decode($row->getParameters());
        if ($parameters instanceof \stdClass) {
            $this->buildQueryParametersFromJson($arguments, $parameters);
        }

        return $arguments;
    }

    private function getThrows(Table\Generated\OperationRow $row, DefinitionsInterface $definitions): array
    {
        $throws = \json_decode($row->getThrows());
        if (!$throws instanceof \stdClass) {
            return [];
        }

        $result = [];
        foreach ($throws as $httpCode => $throw) {
            $schema = $this->schemaManager->getSchema($throw);
            $name = $this->getNameForSchema($throw, $schema);

            $definitions->addSchema($name, $schema);

            $result[] = new Operation\Response($httpCode, TypeFactory::getReference($name));
        }

        return $result;
    }

    private function buildPathParameters(Operation\Arguments $arguments, string $path): void
    {
        $parts = explode('/', $path);
        foreach ($parts as $part) {
            if (isset($part[0])) {
                $name = null;
                if ($part[0] == ':') {
                    $name = substr($part, 1);
                } elseif ($part[0] == '{') {
                    $name = substr($part, 1, -1);
                } elseif ($part[0] == '$') {
                    $pos = strpos($part, '<');
                    if ($pos !== false) {
                        $name = substr($part, 1, $pos - 1);
                    } else {
                        $name = substr($part, 1);
                    }
                }

                if ($name !== null) {
                    $arguments->add($name, new Operation\Argument(ArgumentInterface::IN_PATH, TypeFactory::getString()));
                }
            }
        }
    }

    private function buildQueryParametersFromJson(Operation\Arguments $arguments, \stdClass $parameters): void
    {
        foreach ($parameters as $name => $schema) {
            if (!$schema instanceof \stdClass) {
                continue;
            }

            $arguments->add($name, new Operation\Argument(ArgumentInterface::IN_QUERY, $this->schemaParser->parseType($schema)));
        }
    }

    private function getNameForSchema(string $source, SchemaInterface $schema): string
    {
        $root = $schema->getType();
        if ($root instanceof ReferenceType) {
            return $root->getRef() ?? throw new \RuntimeException('Provided an invalid ref');
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
}
