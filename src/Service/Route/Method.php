<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Api\Specification;
use PSX\Api\SpecificationInterface;
use PSX\Api\Util\Inflection;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Definitions;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Type\StructType;
use PSX\Schema\TypeFactory;
use PSX\Schema\TypeInterface;

/**
 * Method
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Method
{
    /**
     * @var \Fusio\Impl\Table\Route\Method
     */
    private $methodTable;

    /**
     * @var \Fusio\Impl\Table\Route\Response
     */
    private $responseTable;

    /**
     * @var \Fusio\Impl\Table\Scope\Route
     */
    private $scopeTable;

    /**
     * @var Loader
     */
    private $schemaLoader;

    /**
     * @param \Fusio\Impl\Table\Route\Method $methodTable
     * @param \Fusio\Impl\Table\Route\Response $responseTable
     * @param \Fusio\Impl\Table\Scope\Route $scopeTable
     * @param Loader $schemaLoader
     */
    public function __construct(Table\Route\Method $methodTable, Table\Route\Response $responseTable, Table\Scope\Route $scopeTable, Loader $schemaLoader)
    {
        $this->methodTable   = $methodTable;
        $this->responseTable = $responseTable;
        $this->scopeTable    = $scopeTable;
        $this->schemaLoader  = $schemaLoader;
    }

    /**
     * Returns an api resource documentation for the provided route and version
     * 
     * @param integer $routeId
     * @param string $path
     * @param string|null $version
     * @return \PSX\Api\SpecificationInterface
     */
    public function getDocumentation(int $routeId, string $path, ?string $version): SpecificationInterface
    {
        if ($version == '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = $this->methodTable->getVersion($routeId, $version);
        }

        if (empty($version)) {
            throw new StatusCode\UnsupportedMediaTypeException('Version does not exist');
        }

        $definitions = new Definitions();

        $methods  = $this->methodTable->getMethods($routeId, $version, true);
        $resource = new Resource($this->getStatusFromMethods($methods), $path);
        $scopes   = $this->scopeTable->getScopesForRoute($routeId);

        $this->buildPathParameters($path, $resource, $definitions);

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

        return Specification::fromResource($resource, $definitions);
    }

    /**
     * Returns the method configuration for the provide route, version and 
     * request method
     * 
     * @param integer $routeId
     * @param string $version
     * @param string $method
     * @return array
     */
    public function getMethod($routeId, $version, $method)
    {
        if ($version == '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = $this->methodTable->getVersion($routeId, $version);
        }

        if (empty($version)) {
            throw new StatusCode\UnsupportedMediaTypeException('Version does not exist');
        }

        return $this->methodTable->getMethod($routeId, $version, $method);
    }

    /**
     * @param integer $routeId
     * @param string $version
     * @return array
     */
    public function getAllowedMethods($routeId, $version)
    {
        return $this->methodTable->getAllowedMethods($routeId, $version);
    }

    /**
     * @param integer $routeId
     * @param string $version
     * @return array
     */
    public function getRequestSchemas($routeId, $version)
    {
        if ($version == '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = $this->methodTable->getVersion($routeId, $version);
        }

        if (empty($version)) {
            throw new StatusCode\UnsupportedMediaTypeException('Version does not exist');
        }

        $methods = $this->methodTable->getMethods($routeId, $version);
        $schemas = [];

        foreach ($methods as $method) {
            $schemaId = $method['request'];
            if (!empty($schemaId)) {
                $schemas[$method['method']] = $schemaId;
            }
        }

        return $schemas;
    }
    
    private function getStatusFromMethods(array $methods)
    {
        $method = reset($methods);

        return isset($method['status']) ? $method['status'] : Resource::STATUS_DEVELOPMENT;
    }

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
                    $pos  = strpos($part, '<');
                    $name = substr($part, 1, $pos - 1);
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
