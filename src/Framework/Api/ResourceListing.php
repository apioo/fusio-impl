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

namespace Fusio\Impl\Framework\Api;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Table;
use PSX\Api\Exception\InvalidMethodException;
use PSX\Api\Listing\FilterInterface;
use PSX\Api\Listing\Route;
use PSX\Api\Parser\Attribute;
use PSX\Api\Resource;
use PSX\Api\ResourceCollection;
use PSX\Api\Specification;
use PSX\Api\SpecificationInterface;
use PSX\Api\Util\Inflection;
use PSX\Framework\Api\ControllerDocumentation;
use PSX\Framework\Loader\PathMatcher;
use PSX\Framework\Loader\RoutingParserInterface;
use PSX\Schema\Definitions;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Exception\InvalidSchemaException;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\Type\StructType;
use PSX\Schema\TypeFactory;
use PSX\Schema\TypeInterface;

/**
 * ResourceListing
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ResourceListing extends ControllerDocumentation
{
    private RoutingParserInterface $routingParser;
    private Table\Route\Method $methodTable;
    private Table\Route\Response $responseTable;
    private Table\Scope\Route $scopeTable;
    private Loader $schemaLoader;
    private Attribute $attributeParser;

    public function __construct(RoutingParserInterface $routingParser, Table\Route\Method $methodTable, Table\Route\Response $responseTable, Table\Scope\Route $scopeTable, Loader $schemaLoader, SchemaManagerInterface $schemaManager)
    {
        parent::__construct($routingParser, $schemaManager);

        $this->routingParser = $routingParser;
        $this->methodTable = $methodTable;
        $this->responseTable = $responseTable;
        $this->scopeTable = $scopeTable;
        $this->schemaLoader = $schemaLoader;
        $this->attributeParser = new Attribute($schemaManager);
    }

    public function getAvailableRoutes(?FilterInterface $filter = null): iterable
    {
        $collections = $this->routingParser->getCollection($filter);
        $result      = array();

        foreach ($collections as $collection) {
            [$methods, $path, $source] = $collection;

            if ($filter !== null && !$filter->match($path)) {
                continue;
            }

            $result[] = new Route($path, $methods, '*');
        }

        return $result;
    }

    public function find(string $path, ?string $version = null): ?SpecificationInterface
    {
        $matcher    = new PathMatcher($path);
        $collection = $this->routingParser->getCollection();

        foreach ($collection as $route) {
            [$methods, $path, $source, $routeId, $categoryId] = $route;

            if (!$matcher->match($path)) {
                continue;
            }

            if ($source === SchemaApiController::class) {
                return $this->getDocumentation($routeId, $path, $version);
            } else {
                return $this->attributeParser->parse($source, $path);
            }
        }

        return null;
    }

    public function findAll(?string $version = null, FilterInterface $filter = null): SpecificationInterface
    {
        $spec  = new Specification(new ResourceCollection(), new Definitions());
        $index = $this->getAvailableRoutes($filter);

        foreach ($index as $resource) {
            $result = $this->find($resource->getPath(), $version);
            if ($result instanceof SpecificationInterface) {
                $spec->merge($result);
            }
        }

        return $spec;
    }

    protected function newContext(array $route): Context
    {
        $context = new Context();
        $context->setPath($route[1]);
        $context->setSource($route[2]);
        $context->setRouteId($route[3]);
        $context->setCategoryId($route[4]);

        return $context;
    }

    /**
     * Returns an api resource documentation for the provided route and version
     *
     * @throws InvalidSchemaException
     * @throws InvalidMethodException
     */
    public function getDocumentation(int $routeId, string $path, ?string $version = null): SpecificationInterface
    {
        if ($version == '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = $this->methodTable->getVersion($routeId, (int) $version);
        }

        if (empty($version)) {
            throw new \RuntimeException('Version does not exist');
        }

        $definitions = new Definitions();

        $methods  = $this->methodTable->getMethods($routeId, $version, true);
        $resource = new Resource($this->getStatusFromMethods($methods), $path);
        $scopes   = $this->scopeTable->getScopesForRoute($routeId);

        $this->buildPathParameters($path, $resource, $definitions);

        $scopeNames = [];
        foreach ($methods as $method) {
            $resourceMethod = Resource\Factory::getMethod($method['method']);

            if (!empty($method['operation_id'])) {
                $resourceMethod->setOperationId($method['operation_id']);
            } else {
                $resourceMethod->setOperationId($method['action']);
            }

            if (!empty($method['description'])) {
                $resourceMethod->setDescription($method['description']);
            }

            if (!$method['public']) {
                if (isset($scopes[$method['method']])) {
                    $resourceMethod->setSecurity(Authorization::APP, array_unique($scopes[$method['method']]));
                } else {
                    $resourceMethod->setSecurity(Authorization::APP, []);
                }
            }

            if (isset($scopes[$method['method']])) {
                $scopeNames = $scopes[$method['method']];
                $resourceMethod->setTags($scopes[$method['method']]);
            }

            if (!empty($method['parameters'])) {
                $resourceMethod->setQueryParameters($method['parameters']);

                $definitions->addSchema($method['parameters'], $this->schemaLoader->getSchema($method['parameters']));
            }

            if (!empty($method['request'])) {
                $resourceMethod->setRequest($method['request']);

                $definitions->addSchema($method['request'], $this->schemaLoader->getSchema($method['request']));
            }

            $responses = $this->responseTable->getResponses($method['id']);
            if (!empty($responses)) {
                foreach ($responses as $response) {
                    $resourceMethod->addResponse($response['code'], $response['response']);

                    $definitions->addSchema($response['response'], $this->schemaLoader->getSchema($response['response']));
                }
            }

            $resource->addMethod($resourceMethod);
        }

        $resource->setTags($scopeNames);

        return Specification::fromResource($resource, $definitions);
    }


    private function getStatusFromMethods(array $methods)
    {
        $method = reset($methods);

        return $method['status'] ?? Resource::STATUS_DEVELOPMENT;
    }

    /**
     * @throws InvalidSchemaException
     */
    private function buildPathParameters(string $path, Resource $resource, DefinitionsInterface $definitions)
    {
        $type = $this->getPathType($path);
        if (!$type instanceof StructType) {
            return;
        }

        $pathName = Inflection::generateTitleFromRoute($path) . 'Path';
        $definitions->addType($pathName, $type);
        $resource->setPathParameters($pathName);
    }

    /**
     * @throws InvalidSchemaException
     */
    private function getPathType(string $path): ?TypeInterface
    {
        $type = TypeFactory::getStruct();

        $parts = explode('/', $path);
        $count = 0;
        foreach ($parts as $part) {
            if (isset($part[0])) {
                $name = null;
                if ($part[0] == ':') {
                    $name = substr($part, 1);
                } elseif ($part[0] == '$') {
                    $pos = strpos($part, '<');
                    if ($pos !== false) {
                        $name = substr($part, 1, $pos - 1);
                    } else {
                        $name = substr($part, 1);
                    }
                }

                if ($name !== null) {
                    $type->addProperty($name, TypeFactory::getString());
                    $count++;
                }
            }
        }

        return $count > 0 ? $type : null;
    }
}
