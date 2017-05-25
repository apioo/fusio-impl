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

use Fusio\Impl\Adapter\TransformAbstract;
use InvalidArgumentException;
use PSX\Data\Util\CurveArray;
use PSX\Json;
use PSX\Uri\Uri;
use Symfony\Component\Yaml\Parser;

/**
 * Raml
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Raml extends TransformAbstract
{
    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    protected $parser;

    /**
     * @var integer
     */
    protected $version;

    /**
     * @var array
     */
    protected $schemas;

    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }

    public function doParse($data)
    {
        $data = $this->parser->parse($data);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid RAML schema');
        }

        $this->version = $this->parseVersion($data);

        if (isset($data['baseUri'])) {
            $baseUri  = new Uri($data['baseUri']);
            $basePath = $baseUri->getPath();
        } else {
            $basePath = '/';
        }

        if (isset($data['schemas']) && is_array($data['schemas'])) { // 0.8
            $this->schemas = $this->parseSchemas($data['schemas']);
        } elseif (isset($data['types']) && is_array($data['types'])) { // 1.0
            $this->schemas = $this->parseSchemas($data['types']);
        }

        $this->parsePaths($basePath, $data);
    }

    protected function parsePaths($basePath, array $data)
    {
        if (is_array($data)) {
            foreach ($data as $path => $value) {
                if (substr($path, 0, 1) == '/') {
                    $this->parsePath($basePath . '/' . $path, $value);
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

        $this->parsePaths($path, $data);

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
        $request = 'Passthru';
        if (isset($data['body'])) {
            $name    = $this->normalizeName($path . '-' . $methodName . '-request');
            $request = $this->parseSchema($data['body'], $name);

            if (empty($request)) {
                throw new InvalidArgumentException('Found no JSONSchema for ' . $methodName . ' ' . $path . ' request');
            }
        }

        $response = 'Passthru';
        $example  = null;
        if (isset($data['responses']) && is_array($data['responses'])) {
            $codes = [200, 201];
            $body  = null;

            foreach ($codes as $code) {
                if (isset($data['responses'][$code]['body'])) {
                    $body = $data['responses'][$code]['body'];
                    break;
                }
            }

            if (!empty($body)) {
                $name     = $this->normalizeName($path . '-' . $methodName . '-response');
                $response = $this->parseSchema($body, $name, $example);

                if (empty($response)) {
                    throw new InvalidArgumentException('Found no JSONSchema for ' . $methodName . ' ' . $path . ' response');
                }
            }
        }

        if (!empty($example)) {
            $name = $this->normalizeName($path . '-' . $methodName . '-example');

            $this->addAction([
                'name'   => $name,
                'class'  => 'Fusio\Adapter\Util\Action\UtilStaticResponse',
                'config' => [
                    'statusCode' => '200',
                    'response'   => $example,
                ],
            ]);

            $action = $name;
        } else {
            $action = 'Welcome';
        }

        return [
            'active'   => true,
            'public'   => true,
            'action'   => $action,
            'request'  => $request,
            'response' => $response,
        ];
    }

    protected function parseSchema($data, $name, &$example = null)
    {
        foreach ($data as $mediaType => $row) {
            if ($mediaType == 'application/json' && is_array($row)) {
                $schema = $this->extractSchema($row);
                if (!empty($schema)) {
                    // add example if available
                    $example = $this->extractExample($row);

                    $this->addSchema([
                        'name'   => $name,
                        'source' => $schema,
                    ]);

                    return $name;
                }
            }
        }

        return null;
    }

    protected function extractSchema(array $row)
    {
        $schema = null;
        if (isset($row['schema'])) { // 0.8
            $schema = $row['schema'];
        } elseif (isset($row['type'])) { // 1.0
            $schema = $row['type'];
        }

        if (!empty($schema)) {
            if (is_string($schema)) {
                if (strpos($schema, '{') === false) {
                    // check whether we have a reference to a schema
                    if (isset($this->schemas[$schema])) {
                        $schema = $this->schemas[$schema];
                    }

                    // at the moment we cant resolve external files
                    if (substr($schema, 0, 8) == '!include') {
                        throw new InvalidArgumentException('It is not possible to include external files');
                    }
                }

                // check whether we have a json format and prettify
                return Json\Parser::decode($schema, false);
            } elseif (is_array($schema)) {
                return $schema;
            }
        }

        return null;
    }

    protected function extractExample(array $row)
    {
        $example = null;
        if (isset($row['example'])) { // 1.0
            $example = $row['example'];
        }

        if (!empty($example) && is_string($example)) {
            return $example;
        }

        return null;
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
        if (isset($data['version'])) {
            $version = ltrim($data['version'], 'v');
            $version = (int) $version;
            $version = $version > 0 ? $version : 1;
        }

        return $version;
    }

    protected function parseSchemas(array $schemas)
    {
        // @TODO handle !include in schemas

        if (CurveArray::isAssoc($schemas)) {
            return $schemas;
        } else {
            foreach ($schemas as $schemaList) {
                if (is_string($schemaList)) {
                } elseif (CurveArray::isAssoc($schemaList)) {
                    return $schemaList;
                }
            }
        }

        return [];
    }
}
