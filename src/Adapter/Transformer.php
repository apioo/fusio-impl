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

namespace Fusio\Impl\Adapter;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Backend\Model\Action_Config;
use Fusio\Impl\Backend\Model\Action_Create;
use Fusio\Impl\Backend\Model\Route_Create;
use Fusio\Impl\Backend\Model\Route_Method;
use Fusio\Impl\Backend\Model\Route_Method_Responses;
use Fusio\Impl\Backend\Model\Route_Methods;
use Fusio\Impl\Backend\Model\Route_Version;
use Fusio\Impl\Backend\Model\Schema_Create;
use Fusio\Impl\Backend\Model\Schema_Source;
use PSX\Api\Parser;
use PSX\Api\Resource;
use PSX\Json;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Generator;
use PSX\Schema\Schema;
use PSX\Schema\SchemaResolver;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class which uses the psx api classes to parse and transform an API 
 * specification to a Fusio adapter format
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Transformer
{
    const TYPE_OPENAPI = 1;

    /**
     * @var integer
     */
    private $apiVersion;

    /**
     * @var \PSX\Schema\Generator\TypeSchema
     */
    private $generator;

    /**
     * @var array
     */
    private $routes;

    /**
     * @var array
     */
    private $schema;

    /**
     * @var array
     */
    private $action;

    public function __construct($apiVersion = 1)
    {
        $this->apiVersion = (integer) $apiVersion;
        $this->generator  = new Generator\TypeSchema();
    }

    public function transform($type, $schema)
    {
        // check whether we need to transform YAML into JSON
        if (!json_decode($schema)) {
            try {
                $data   = Yaml::parse($schema);
                $schema = json_encode($data);
            } catch (ParseException $e) {
                // invalid YAML syntax
            }
        }

        $this->routes = [];
        $this->schema = [];
        $this->action = [];

        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('PSX\\Schema\\Annotation');
        $parser = new Parser\OpenAPI($reader);

        $specification = $parser->parse($schema);
        $definitions = $specification->getDefinitions();

        foreach ($definitions->getTypes(DefinitionsInterface::SELF_NAMESPACE) as $name => $type) {
            $schema = new Schema($type, clone $definitions);
            (new SchemaResolver())->resolve($schema);

            $data = \json_decode($this->generator->generate($schema));

            $source = new Schema_Source();
            foreach ($data as $key => $value) {
                $source->setProperty($key, $value);
            }

            $schema = new Schema_Create();
            $schema->setName($name);
            $schema->setSource($source);
            $this->addSchema($schema);
        }

        $resources = $specification->getResourceCollection();
        foreach ($resources as $path => $resource) {
            $this->doParse($resource);
        }

        return $this->build();
    }

    private function build()
    {
        $data = [];

        if (!empty($this->routes)) {
            $data['routes'] = array_values($this->routes);
        }

        if (!empty($this->action)) {
            $data['action'] = array_values($this->action);
        }

        if (!empty($this->schema)) {
            $data['schema'] = array_values($this->schema);
        }

        return Json\Parser::encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @param \PSX\Api\Resource $resource
     */
    private function doParse(Resource $resource)
    {
        $methods = $resource->getMethods();
        $config  = new Route_Methods();
        $prefix  = $this->buildPrefixFromPath($resource->getPath());

        foreach ($methods as $methodName => $method) {
            $config[$methodName] = $this->parseMethod($method, $prefix);
        }

        $version = new Route_Version();
        $version->setVersion($this->apiVersion);
        $version->setStatus(Resource::STATUS_DEVELOPMENT);
        $version->setMethods($config);

        $route = new Route_Create();
        $route->setPath($this->normalizePath($resource->getPath()));
        $route->setConfig([$version]);

        $this->addRoute($route);
    }

    private function parseMethod(Resource\MethodAbstract $method, $prefix): Route_Method
    {
        $result = new Route_Method();
        $result->setActive(true);
        $result->setPublic(true);

        if ($method->hasQueryParameters()) {
            $result->setParameters($method->getQueryParameters());
        }

        if ($method->hasRequest()) {
            $result->setRequest($method->getRequest());
        }

        $responses  = new Route_Method_Responses();
        $statusCode = null;

        foreach ($method->getResponses() as $code => $schema) {
            $responses->setProperty($code, $schema);

            if ($code >= 200 && $code < 300) {
                $statusCode = $code;
            }
        }

        $result->setResponses($responses);

        $name = $this->buildName([$prefix, $method->getOperationId(), $method->getName()]);
        $result->setAction($name);

        $config = new Action_Config();
        $config->setProperty('statusCode', strval($statusCode ?? 200));
        // @TODO maybe get the response from the example payload
        $config->setProperty('response', json_encode(['message' => 'Test implementation']));

        $action = new Action_Create();
        $action->setName($name);
        $action->setClass(UtilStaticResponse::class);
        $action->setEngine(PhpClass::class);
        $action->setConfig($config);
        $this->addAction($action);

        return $result;
    }

    private function buildName(array $parts)
    {
        $parts = array_map(function($value){
            return preg_replace('/[^0-9A-Za-z_-]/', '_', $value);
        }, $parts);

        return implode('-', array_filter($parts));
    }

    private function buildPrefixFromPath($path)
    {
        return implode('-', array_filter(explode('/', $path)));
    }

    private function normalizePath($path)
    {
        $path = '/' . implode('/', array_filter(explode('/', $path)));
        $path = preg_replace('/(\{(\w+)\})/i', ':$2', $path);
        return $path;
    }

    private function addRoute(Route_Create $route)
    {
        $this->routes[$route->getPath()] = $route;
    }

    private function addSchema(Schema_Create $schema)
    {
        $this->schema[$schema->getName()] = $schema;
    }

    private function addAction(Action_Create $action)
    {
        $this->action[$action->getName()] = $action;
    }

    public static function fromSchema($format, $data)
    {
        return (new self())->transform(self::TYPE_OPENAPI, $data);
    }
}
