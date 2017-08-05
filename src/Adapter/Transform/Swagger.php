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

namespace Fusio\Impl\Adapter\Transform;

use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Impl\Adapter\TransformAbstract;
use InvalidArgumentException;
use PSX\Uri\Uri;

/**
 * Swagger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Swagger extends TransformAbstract
{
    /**
     * @var integer
     */
    protected $version;

    /**
     * @var array
     */
    protected $definitions;

    public function doParse($data)
    {
        $data = json_decode($data, true);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid Swagger schema');
        }

        $this->version = $this->parseVersion($data);

        if (isset($data['basePath'])) {
            if (isset($data['resourcePath'])) { // 1.2
                $basePath = $data['resourcePath'];
            } else {
                // in 1.2 base path is an url in 2.0 it is a path
                $baseUri  = new Uri($data['basePath']);
                $basePath = $baseUri->getPath();
            }
        } else {
            $basePath = '/';
        }

        if (isset($data['definitions']) && is_array($data['definitions'])) { // 2.0
            $this->parseDefinitions($data['definitions']);
            $this->resolveDefinitions();
        } elseif (isset($data['models']) && is_array($data['models'])) { // 1.2
            $this->parseDefinitions($data['models']);
            $this->resolveDefinitions();
        }

        $this->parsePaths($basePath, $data);
    }

    protected function parsePaths($basePath, array $data)
    {
        if (isset($data['paths']) && is_array($data['paths'])) { // 2.0
            foreach ($data['paths'] as $path => $value) {
                if (substr($path, 0, 1) == '/') {
                    $this->parsePath($basePath . '/' . $path, $value);
                }
            }
        } elseif (isset($data['apis']) && is_array($data['apis'])) { // 1.2
            foreach ($data['paths'] as $value) {
                if (isset($value['path']) && isset($value['operations']) && is_array($value['operations'])) {
                    $this->parsePath12($basePath . '/' . $value['path'], $value['operations']);
                }
            }
        }
    }

    protected function parsePath($path, array $data)
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $path    = $this->normalizePath($path);
        $config  = [];

        $data = array_change_key_case($data, CASE_UPPER);

        foreach ($methods as $method) {
            if (isset($data[$method])) {
                $config[$method] = $this->parseMethod($method, $data[$method], $path);
            }
        }

        $this->addRoute([
            'path'   => $path,
            'config' => [[
                'version' => (integer) $this->version,
                'status'  => 4,
                'methods' => $config
            ]],
        ]);
    }

    protected function parsePath12($path, array $data)
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $path    = $this->normalizePath($path);
        $config  = [];

        foreach ($data as $operation) {
            if (isset($operation['method'])) {
                $method = strtoupper($operation['method']);
                
                if (in_array($method, $methods)) {
                    $config[$method] = $this->parseMethod12($method, $operation, $path);
                }
            }
        }

        $this->addRoute([
            'path'   => $path,
            'config' => [[
                'version' => (integer) $this->version,
                'status'  => 4,
                'methods' => $config
            ]],
        ]);
    }

    protected function parseMethod($methodName, array $data, $path)
    {
        if (isset($data['operationId'])) {
            $prefix = $data['operationId'];
        } else {
            $prefix = $path;
        }

        $version = [
            'active' => true,
            'public' => true,
        ];

        // parameters
        // @TODO handle query parameters
        $version['parameters'] = 'Passthru';

        // request
        $request = null;
        if (isset($data['parameters']) && is_array($data['parameters'])) {
            $name = $this->normalizeName($prefix . '-' . $methodName . '-request');

            foreach ($data['parameters'] as $parameter) {
                if ($parameter['in'] == 'body' && isset($parameter['schema'])) {
                    $request = $this->parseSchema($parameter['schema'], $name);
                    break;
                }
            }
        }

        if (!empty($request)) {
            $version['request'] = $request;
        } else {
            $version['request'] = 'Passthru';
        }

        // response
        $responses = [];
        $example   = null;
        if (isset($data['responses']) && is_array($data['responses'])) {
            foreach ($data['responses'] as $code => $response) {
                // get success json example
                if ($code >= 200 && $code <= 299 && empty($example)) {
                    if (isset($response['examples']) && isset($response['examples']['application/json'])) {
                        $schema = $response['examples']['application/json'];
                        if (is_array($schema)) {
                            $example = json_encode($schema, JSON_PRETTY_PRINT);
                        } elseif (is_string($schema)) {
                            $example = $schema;
                        }
                    }
                }

                if (isset($response['schema']) && is_array($response['schema'])) {
                    $code = intval($code);
                    if ($code >= 200) {
                        $name = $this->normalizeName($prefix . '-' . $methodName . '-' . $code . '-response');
                        $resp = $this->parseSchema($response['schema'], $name);

                        $responses[$code] = $resp;
                    }
                }
            }
        }

        if (!empty($responses)) {
            $version['responses'] = $responses;
        } else {
            $version['responses'] = [200 => 'Passthru'];
        }

        if (!empty($example)) {
            $name = $this->normalizeName($prefix);

            $this->addAction([
                'name'   => $name,
                'class'  => UtilStaticResponse::class,
                'config' => [
                    'statusCode' => '200',
                    'response'   => $example,
                ],
            ]);

            $version['action'] = $name;
        } else {
            $version['action'] = 'Welcome';
        }

        return $version;
    }

    protected function parseMethod12($methodName, array $data, $path)
    {
        if (isset($data['nickname'])) {
            $prefix = $data['nickname'];
        } else {
            $prefix = $path;
        }

        $version = [
            'active' => true,
            'public' => true,
        ];

        // parameters
        // @TODO handle query parameters
        $version['parameters'] = 'Passthru';

        // request
        $request = null;
        if (isset($data['parameters']) && is_array($data['parameters'])) {
            $name = $this->normalizeName($prefix . '-' . $methodName . '-request');
            foreach ($data['parameters'] as $parameter) {
                if (isset($parameter['name']) && $parameter['name'] == 'body' && isset($parameter['type'])) {
                    if (isset($this->definitions[$parameter['type']])) {
                        $request = $this->parseSchema($this->definitions[$parameter['type']], $name);
                        break;
                    }
                }
            }
        }
        
        if (!empty($request)) {
            $version['request'] = $request;
        } else {
            $version['request'] = 'Passthru';
        }

        // response
        $responses = [];
        if (isset($data['responseMessages']) && is_array($data['responseMessages'])) {
            foreach ($data['responseMessages'] as $responseMessage) {
                if (isset($responseMessage['responseModel']) && isset($responseMessage['code'])) {
                    
                    if (isset($this->definitions[$responseMessage['responseModel']])) {
                        $code = intval($responseMessage['code']);
                        if ($code >= 200) {
                            $name = $this->normalizeName($prefix . '-' . $methodName . '-' . $code . '-response');
                            $this->parseSchema($this->definitions[$responseMessage['responseModel']], $name);

                            $responses[$code] = $name;
                        }
                    }
                }
            }
        }

        if (!empty($responses)) {
            $version['responses'] = $responses;
        } else {
            $version['responses'] = [200 => 'Passthru'];
        }

        // action
        $action = 'Welcome';

        $version['action'] = $action;

        return $version;
    }

    protected function parseSchema($data, $name)
    {
        $this->addSchema([
            'name'   => $name,
            'source' => $this->resolveRefs($data),
        ]);

        return $name;
    }

    protected function normalizePath($path)
    {
        $path = '/' . implode('/', array_filter(explode('/', $path)));
        $path = preg_replace('/(\{(\w+)\})/i', ':$2', $path);

        return $path;
    }

    protected function normalizeName($name)
    {
        $name = ltrim($name, '/');
        $name = str_replace('/', '-', $name);
        $name = preg_replace('/[^\dA-z0-9\-\_]/i', '', $name);

        return $name;
    }

    protected function parseVersion(array $data)
    {
        $version = 1;
        if (isset($data['info']) && isset($data['info']['version'])) {
            $version = $data['info']['version'];
        } elseif (isset($data['apiVersion'])) {
            $version = $data['apiVersion'];
        }

        $version = ltrim($version, 'v');
        $version = (int) $version;
        $version = $version > 0 ? $version : 1;

        return $version;
    }

    protected function parseDefinitions(array $schemas)
    {
        foreach ($schemas as $name => $schema) {
            if (!isset($schema['type'])) {
                $schema['type'] = 'object';
            }

            $this->definitions[$name] = $schema;
        }
    }

    protected function resolveDefinitions()
    {
        foreach ($this->definitions as $name => $schema) {
            $this->definitions[$name] = $this->resolveRefs($schema);
        }
    }

    protected function resolveRefs(array $data, $depth = 0)
    {
        if ($depth > 16) {
            throw new InvalidArgumentException('Max nesting reached');
        }

        if (isset($data['$ref'])) {
            if (strpos($data['$ref'], '#/definitions/') === 0) {
                $ref = substr($data['$ref'], 14);
                if (isset($this->definitions[$ref])) {
                    return $this->resolveRefs($this->definitions[$ref]);
                } else {
                    throw new InvalidArgumentException('Could not resolve reference ' . $data['$ref']);
                }
            } else {
                throw new InvalidArgumentException('Can only resolve local references');
            }
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->resolveRefs($value, $depth + 1);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
