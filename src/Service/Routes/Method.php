<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Routes;

use Fusio\Engine\Schema\LoaderInterface;
use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Schema\LazySchema;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Parser\JsonSchema\Document;
use PSX\Schema\Parser\JsonSchema\RefResolver;
use PSX\Schema\Schema;
use PSX\Schema\SchemaInterface;

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
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $methodTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Response
     */
    protected $responseTable;

    /**
     * @var \Fusio\Impl\Table\Scope\Route
     */
    protected $scopeTable;

    /**
     * @var \Fusio\Engine\Schema\LoaderInterface
     */
    protected $schemaLoader;

    /**
     * @var \PSX\Schema\Parser\JsonSchema\RefResolver
     */
    protected $resolver;

    /**
     * @param \Fusio\Impl\Table\Routes\Method $methodTable
     * @param \Fusio\Impl\Table\Routes\Response $responseTable
     * @param \Fusio\Impl\Table\Scope\Route $scopeTable
     * @param \Fusio\Engine\Schema\LoaderInterface $schemaLoader
     */
    public function __construct(Table\Routes\Method $methodTable, Table\Routes\Response $responseTable, Table\Scope\Route $scopeTable, LoaderInterface $schemaLoader)
    {
        $this->methodTable   = $methodTable;
        $this->responseTable = $responseTable;
        $this->scopeTable    = $scopeTable;
        $this->schemaLoader  = $schemaLoader;
        $this->resolver      = RefResolver::createDefault();
    }

    /**
     * Returns an api resource documentation for the provided route and version
     * 
     * @param integer $routeId
     * @param string $version
     * @param string $path
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($routeId, $version, $path)
    {
        if ($version == '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = $this->methodTable->getVersion($routeId, $version);
        }

        if (empty($version)) {
            throw new StatusCode\UnsupportedMediaTypeException('Version does not exist');
        }

        $methods  = $this->methodTable->getMethods($routeId, $version, true, true);
        $resource = new Resource($this->getStatusFromMethods($methods), $path);
        $scopes   = $this->scopeTable->getScopesForRoute($routeId);

        foreach ($methods as $method) {
            $resourceMethod = Resource\Factory::getMethod($method['method']);
            $schemaCache    = $method['schemaCache'];

            if (!$method['public']) {
                if (isset($scopes[$method['method']])) {
                    $resourceMethod->setSecurity(Authorization::APP, array_unique($scopes[$method['method']]));
                } else {
                    $resourceMethod->setSecurity(Authorization::APP, []);
                }
            }

            if ($method['status'] != Resource::STATUS_DEVELOPMENT && !empty($schemaCache)) {
                // if we are not in development mode and a cache is available
                // use it
                $spec = json_decode($schemaCache, true);

                if (isset($spec['parameters'])) {
                    $property   = $this->getProperty($spec['parameters']);
                    $properties = $property->getProperties();

                    if (!empty($properties)) {
                        foreach ($properties as $name => $type) {
                            $resourceMethod->addQueryParameter($name, $type);
                        }
                    }
                }

                if (isset($spec['request'])) {
                    $resourceMethod->setRequest(new Schema($this->getProperty($spec['request'])));
                }

                if (isset($spec['responses'])) {
                    foreach ($spec['responses'] as $code => $schema) {
                        $resourceMethod->addResponse($code, new Schema($this->getProperty($schema)));
                    }
                }
            } else {
                if ($method['parameters'] > 0) {
                    $schema     = $this->schemaLoader->getSchema($method['parameters']);
                    $properties = $schema->getDefinition()->getProperties();

                    if (!empty($properties)) {
                        foreach ($properties as $name => $type) {
                            $resourceMethod->addQueryParameter($name, $type);
                        }
                    }
                }

                if ($method['request'] > 0) {
                    $resourceMethod->setRequest(new LazySchema($this->schemaLoader, $method['request']));
                }

                $responses = $this->responseTable->getResponses($method['id']);
                if (!empty($responses)) {
                    foreach ($responses as $response) {
                        $resourceMethod->addResponse($response['code'], new LazySchema($this->schemaLoader, $response['response']));
                    }
                }
            }

            $resource->addMethod($resourceMethod);
        }

        return $resource;
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

    public function getAllowedMethods($routeId, $version)
    {
        return $this->methodTable->getAllowedMethods($routeId, $version);
    }

    private function getStatusFromMethods(array $methods)
    {
        $method = reset($methods);

        return isset($method['status']) ? $method['status'] : Resource::STATUS_DEVELOPMENT;
    }

    /**
     * @param array $schema
     * @return \PSX\Schema\PropertyInterface
     */
    private function getProperty(array $schema)
    {
        $document = new Document($schema, $this->resolver);
        $this->resolver->setRootDocument($document);

        return $document->getProperty();
    }
}
