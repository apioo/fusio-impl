<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service\Operation;

use Fusio\Impl\Table;
use PSX\Api\Operation;
use PSX\Api\Operation\ArgumentInterface;
use PSX\Api\Specification;
use PSX\Api\SpecificationInterface;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\Type\StructType;
use PSX\Schema\TypeFactory;

/**
 * Service which builds a specification based on the schemas defined at the database
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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

        $return = $this->getReturn($row, $specification->getDefinitions());

        $operation = new Operation($row->getHttpMethod(), $row->getHttpPath(), $return);
        $operation->setArguments($this->getArguments($row, $specification->getDefinitions()));
        $operation->setThrows($this->getThrows($row));

        if (!empty($row->getDescription())) {
            $operation->setDescription($row->getDescription());
        }

        $operation->setTags($scopes);

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

        $definitions->addSchema($outgoing, $this->schemaManager->getSchema($outgoing));

        return new Operation\Response($row->getHttpCode(), TypeFactory::getReference($outgoing));
    }

    private function getArguments(Table\Generated\OperationRow $row, DefinitionsInterface $definitions): Operation\Arguments
    {
        $arguments = new Operation\Arguments();

        $this->buildPathParameters($arguments, $row->getHttpPath());

        $incoming = $row->getIncoming();
        if (!empty($incoming)) {
            $definitions->addSchema($incoming, $this->schemaManager->getSchema($incoming));

            $arguments->add('payload', new Operation\Argument(ArgumentInterface::IN_BODY, TypeFactory::getReference($incoming)));
        }

        $parameters = \json_decode($row->getParameters());
        if ($parameters instanceof \stdClass) {
            $this->buildQueryParametersFromJson($arguments, $parameters);
        }

        return $arguments;
    }

    private function getThrows(Table\Generated\OperationRow $row): array
    {
        $throws = \json_decode($row->getThrows());
        if (!$throws instanceof \stdClass) {
            return [];
        }

        $result = [];
        foreach ($throws as $httpCode => $schema) {
            $result[] = new Operation\Response($httpCode, TypeFactory::getReference($schema));
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
        foreach ($parameters as $name => $parameter) {
            $in = $parameter->in ?? ArgumentInterface::IN_QUERY;
            if (empty($in) || !is_string($in)) {
                continue;
            }

            $schema = $parameter->schema ?? null;
            if (!$schema instanceof \stdClass) {
                continue;
            }

            $arguments->add($name, new Operation\Argument($in, $this->schemaParser->parseType($schema)));
        }
    }

    private function buildQueryParametersFromSchema(Operation\Arguments $arguments, string $parameters): void
    {
        $schema = $this->schemaManager->getSchema($parameters);
        if (!$schema instanceof StructType) {
            return;
        }

        foreach ($schema->getProperties() as $name => $property) {
            $arguments->add($name, new Operation\Argument(ArgumentInterface::IN_QUERY, $property));
        }
    }
}
