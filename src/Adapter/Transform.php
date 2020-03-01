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

use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Engine\Factory\Resolver\PhpClass;
use PSX\Api\Parser;
use PSX\Api\Resource;
use PSX\Json;
use PSX\Schema\Generator;
use PSX\Schema\Parser\JsonSchema\RefResolver;
use PSX\Schema\Schema;
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
class Transform
{
    const TYPE_OPENAPI = 1;
    const TYPE_RAML = 2;
    const TYPE_SWAGGER = 3;

    /**
     * @var integer
     */
    private $apiVersion;

    /**
     * @var \PSX\Schema\Generator\JsonSchema
     */
    private $generator;

    /**
     * @var array
     */
    protected $routes;

    /**
     * @var array
     */
    protected $schema;

    /**
     * @var array
     */
    protected $action;

    public function __construct($apiVersion = 1)
    {
        $this->apiVersion = (integer) $apiVersion;
        $this->generator  = new Generator\JsonSchema();
    }

    public function transform($type, $schema)
    {
        // check whether we need to transform YAML into JSON
        if (in_array($type, [self::TYPE_OPENAPI, self::TYPE_SWAGGER])) {
            if (!json_decode($schema)) {
                try {
                    $data   = Yaml::parse($schema);
                    $schema = json_encode($data);
                } catch (ParseException $e) {
                    // invalid YAML syntax
                }
            }
        }

        $this->routes = [];
        $this->schema = [];
        $this->action = [];
        
        $parser     = $this->newParserByType($type);
        $collection = $parser->parseAll($schema);

        foreach ($collection as $path => $resource) {
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
        $config  = [];
        $prefix  = $this->buildPrefixFromPath($resource->getPath());

        foreach ($methods as $methodName => $method) {
            $config[$methodName] = $this->parseMethod($method, $prefix);
        }

        $this->addRoute($prefix, [
            'path'   => $this->normalizePath($resource->getPath()),
            'config' => [[
                'version' => $this->apiVersion,
                'status'  => Resource::STATUS_DEVELOPMENT,
                'methods' => $config
            ]],
        ]);
    }

    private function parseMethod(Resource\MethodAbstract $method, $prefix)
    {
        $version = [
            'active' => true,
            'public' => true,
        ];

        if ($method->hasQueryParameters()) {
            $schema = new Schema($method->getQueryParameters());
            $name   = $this->buildName([$prefix, $method->getOperationId(), $method->getName(), 'query']);
            $source = $this->generator->generate($schema);

            $this->addSchema($name, [
                'name'   => $name,
                'source' => json_decode($source),
            ]);

            $version['parameters'] = $name;
        }

        if ($method->hasRequest()) {
            $schema = $method->getRequest();
            $name   = $this->buildName([$prefix, $method->getOperationId(), $method->getName(), 'request']);
            $source = $this->generator->generate($schema);

            $this->addSchema($name, [
                'name'   => $name,
                'source' => json_decode($source),
            ]);

            $version['request'] = $name;
        }

        $responses  = $method->getResponses();
        $resps      = [];
        $statusCode = null;

        foreach ($responses as $code => $schema) {
            $name   = $this->buildName([$prefix, $method->getOperationId(), $method->getName(), $code, 'response']);
            $source = $this->generator->generate($schema);

            $this->addSchema($name, [
                'name'   => $name,
                'source' => json_decode($source),
            ]);

            $resps[$code] = $name;

            if ($code >= 200 && $code < 300) {
                $statusCode = $code;
            }
        }

        if (!empty($resps)) {
            $version['responses'] = $resps;
        }

        $name = $this->buildName([$prefix, $method->getOperationId(), $method->getName()]);

        $this->addAction($name, [
            'name'   => $name,
            'class'  => UtilStaticResponse::class,
            'engine' => PhpClass::class,
            'config' => [
                'statusCode' => strval($statusCode),
                'response'   => json_encode(['message' => 'Test implementation']),
            ],
        ]);

        $version['action'] = $name;

        return $version;
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

    private function addRoute($path, array $route)
    {
        $this->routes[$path] = $route;
    }

    private function addSchema($name, array $schema)
    {
        $this->schema[$name] = $schema;
    }

    private function addAction($name, array $action)
    {
        $this->action[$name] = $action;
    }

    /**
     * @param string $type
     * @return \PSX\Api\ParserCollectionInterface
     */
    private function newParserByType($type)
    {
        switch ($type) {
            case self::TYPE_OPENAPI:
                $resolver = new RefResolver();

                return new Parser\OpenAPI(null, $resolver);
                break;

            case self::TYPE_RAML:
                return new Parser\Raml();
                break;

            case self::TYPE_SWAGGER:
                $resolver = new RefResolver();

                return new Parser\Swagger(null, $resolver);
                break;
        }

        throw new \InvalidArgumentException('Invalid parser type');
    }

    public static function fromSchema($format, $data)
    {
        $transformer = new self();

        switch ($format) {
            case 'openapi':
                return $transformer->transform(self::TYPE_OPENAPI, $data);
                break;

            case 'raml':
                return $transformer->transform(self::TYPE_RAML, $data);
                break;

            case 'swagger':
                return $transformer->transform(self::TYPE_SWAGGER, $data);
                break;
                
            default:
                throw new \RuntimeException('Invalid format');
        }
    }
}
