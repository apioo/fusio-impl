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

namespace Fusio\Impl\Service\Route;

use Fusio\Impl\Service\Schema\Loader;
use Fusio\Impl\Table;
use PSX\Api\Operation;
use PSX\Api\Operation\ArgumentInterface;
use PSX\Api\Specification;
use PSX\Api\SpecificationInterface;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Parser\TypeSchema;
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
    private Table\Route $routeTable;
    private Table\Route\Method $methodTable;
    private Table\Route\Response $responseTable;
    private Table\Scope\Route $scopeTable;
    private Loader $schemaLoader;
    private TypeSchema $schemaParser;

    public function __construct(Table\Route $routeTable, Table\Route\Method $methodTable, Table\Route\Response $responseTable, Table\Scope\Route $scopeTable, Loader $schemaLoader)
    {
        $this->routeTable = $routeTable;
        $this->methodTable = $methodTable;
        $this->responseTable = $responseTable;
        $this->scopeTable = $scopeTable;
        $this->schemaLoader = $schemaLoader;
        $this->schemaParser = new TypeSchema();
    }

    public function build(int $routeId): SpecificationInterface
    {
        $route = $this->routeTable->find($routeId);
        if (!$route instanceof Table\Generated\RoutesRow) {
            throw new \RuntimeException('Provided an invalid route');
        }

        $version = $this->methodTable->getLatestVersion($routeId);
        if (empty($version)) {
            throw new \RuntimeException('Version does not exist');
        }

        $specification = new Specification();
        $path = $route->getPath();
        $methods = $this->methodTable->getMethods($routeId, $version, true);
        $scopes = $this->scopeTable->getScopesForRoute($routeId);

        foreach ($methods as $method) {
            $return = $this->getReturn($method['id'], $specification->getDefinitions());
            if (empty($return)) {
                continue;
            }

            $operation = new Operation($method['method'], $path, $return);
            $operation->setArguments($this->getArguments($path, $method, $specification->getDefinitions()));
            $operation->setThrows($this->getThrows($method['id'], $specification->getDefinitions()));

            if (!empty($method['description'])) {
                $operation->setDescription($method['description']);
            }

            if (isset($scopes[$method['method']])) {
                $operation->setTags($scopes[$method['method']]);

                if (!$method['public']) {
                    $operation->setSecurity($scopes[$method['method']]);
                }
            }

            $operationId = !empty($method['operation_id']) ? $method['operation_id'] : $method['action'];
            $specification->getOperations()->add($operationId, $operation);
        }

        return $specification;
    }

    private function getReturn(int $methodId, DefinitionsInterface $definitions): ?Operation\Response
    {
        $responses = $this->responseTable->getResponses($methodId, 200, 299);
        $response = $responses[0] ?? null;
        if (empty($response)) {
            return null;
        }

        $definitions->addSchema($response['response'], $this->schemaLoader->getSchema($response['response']));

        return new Operation\Response($response['code'], TypeFactory::getReference($response['response']));
    }

    private function getArguments(string $path, array $method, DefinitionsInterface $definitions): Operation\Arguments
    {
        $arguments = new Operation\Arguments();

        $this->buildPathParameters($arguments, $path);

        if (!empty($method['request'])) {
            $definitions->addSchema($method['request'], $this->schemaLoader->getSchema($method['request']));

            $arguments->add('payload', new Operation\Argument(ArgumentInterface::IN_BODY, TypeFactory::getReference($method['request'])));
        }

        if (!empty($method['parameters'])) {
            $parameters = \json_decode($method['parameters']);
            if ($parameters instanceof \stdClass) {
                $this->buildQueryParametersFromJson($arguments, $parameters);
            } else {
                $this->buildQueryParametersFromSchema($arguments, $method['parameters']);

            }
        }

        return $arguments;
    }

    private function getThrows(int $methodId, DefinitionsInterface $definitions): array
    {
        $result = [];
        $responses = $this->responseTable->getResponses($methodId, 400, 599);
        foreach ($responses as $response) {
            $definitions->addSchema($response['response'], $this->schemaLoader->getSchema($response['response']));

            $result[] = new Operation\Response($response['code'], TypeFactory::getReference($response['response']));
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
        $schema = $this->schemaLoader->getSchema($parameters);
        if (!$schema instanceof StructType) {
            return;
        }

        foreach ($schema->getProperties() as $name => $property) {
            $arguments->add($name, new Operation\Argument(ArgumentInterface::IN_QUERY, $property));
        }
    }
}