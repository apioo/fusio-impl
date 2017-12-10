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

namespace Fusio\Impl\Service\System\Deploy\Transformer;

use Fusio\Impl\Backend;
use Fusio\Impl\Service\System\Deploy\IncludeDirective;
use Fusio\Impl\Service\System\Deploy\NameGenerator;
use Fusio\Impl\Service\System\Deploy\TransformerInterface;
use Fusio\Impl\Service\System\SystemAbstract;
use PSX\Json\Parser;
use PSX\Json\Pointer;
use PSX\Uri\Uri;
use RuntimeException;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Schema implements TransformerInterface
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $resolvedSchemas = $this->resolveSchemasFromRoutes($data, $basePath);

        $schema = isset($data[SystemAbstract::TYPE_SCHEMA]) ? $data[SystemAbstract::TYPE_SCHEMA] : [];

        if (!empty($resolvedSchemas)) {
            if (is_array($schema)) {
                $schema = array_merge($schema, $resolvedSchemas);
            } else {
                $schema = $resolvedSchemas;
            }
        }

        if (!empty($schema) && is_array($schema)) {
            $result = [];
            foreach ($schema as $name => $entry) {
                $result[] = $this->transformSchema($name, $entry, $basePath);
            }
            $import->schema = $result;
        }
    }

    protected function transformSchema($name, $schema, $basePath)
    {
        return [
            'name'   => $name,
            'source' => $this->resolveSchema($schema, $basePath),
        ];
    }

    private function resolveSchema($data, $basePath)
    {
        if (is_string($data)) {
            if (substr($data, 0, 8) == '!include') {
                $file = $basePath . '/' . substr($data, 9);

                if (is_file($file)) {
                    return $this->resolveFile($file);
                } else {
                    throw new RuntimeException('Could not resolve file: ' . $file);
                }
            } else {
                return $this->traverseSchema(Parser::decode($data), $basePath);
            }
        } elseif (is_array($data) || $data instanceof \stdClass) {
            return $this->traverseSchema($data, $basePath);
        } else {
            throw new RuntimeException('Schema must be a string or array');
        }
    }

    /**
     * @param string $file
     * @param string $fragment
     * @return mixed
     */
    private function resolveFile($file, $fragment = null)
    {
        if (!is_file($file)) {
            throw new RuntimeException('Could not resolve schema ' . $file);
        }

        $basePath = pathinfo($file, PATHINFO_DIRNAME);
        $data     = Parser::decode(file_get_contents($file));

        if (!empty($fragment)) {
            $pointer = new Pointer($fragment);
            $data    = $pointer->evaluate($data);
        }

        return $this->traverseSchema($data, $basePath);
    }

    /**
     * @param mixed $data
     * @param string $basePath
     * @return mixed
     */
    private function traverseSchema($data, $basePath)
    {
        if ($data instanceof \stdClass) {
            if (isset($data->{'$ref'}) && is_string($data->{'$ref'})) {
                $uri = new Uri($data->{'$ref'});

                if ($uri->isAbsolute()) {
                    if ($uri->getScheme() == 'file') {
                        return $this->resolveFile($basePath . '/' . $uri->getPath(), $uri->getFragment());
                    } elseif ($uri->getScheme() == 'schema') {
                        // schema scheme is allowed
                    } else {
                        throw new RuntimeException('Scheme ' . $uri->getScheme() . ' is not supported');
                    }
                }
            }

            $object = new \stdClass();
            foreach ($data as $key => $value) {
                $object->{$key} = $this->traverseSchema($value, $basePath);
            }
            return $object;
        } elseif (is_array($data)) {
            $array = [];
            foreach ($data as $value) {
                $array[] = $this->traverseSchema($value, $basePath);
            }
            return $array;
        } else {
            return $data;
        }
    }

    /**
     * In case the routes contains an include as request/response schemas we
     * automatically create a fitting schema entry
     *
     * @param array $data
     * @param string $basePath
     * @return array
     */
    private function resolveSchemasFromRoutes(array $data, $basePath)
    {
        $schemas = [];
        $type    = SystemAbstract::TYPE_ROUTES;

        if (isset($data[$type]) && is_array($data[$type])) {
            foreach ($data[$type] as $name => $row) {
                // resolve includes
                $row = IncludeDirective::resolve($row, $basePath, $type);

                if (isset($row['methods']) && is_array($row['methods'])) {
                    foreach ($row['methods'] as $method => $config) {
                        // parameters
                        if (isset($config['parameters']) && !$this->isName($config['parameters'])) {
                            $schema = $this->resolveSchema($config['parameters'], $basePath);
                            $name   = NameGenerator::getSchemaNameFromSource($config['parameters']);

                            $schemas[$name] = $schema;
                        }

                        // request
                        if (isset($config['request']) && !$this->isName($config['request'])) {
                            $schema = $this->resolveSchema($config['request'], $basePath);
                            $name   = NameGenerator::getSchemaNameFromSource($config['request']);

                            $schemas[$name] = $schema;
                        }

                        // responses
                        if (isset($config['response']) && !$this->isName($config['response'])) {
                            $schema = $this->resolveSchema($config['response'], $basePath);
                            $name   = NameGenerator::getSchemaNameFromSource($config['response']);

                            $schemas[$name] = $schema;
                        } elseif (isset($config['responses']) && is_array($config['responses'])) {
                            foreach ($config['responses'] as $code => $response) {
                                if (!$this->isName($response)) {
                                    $schema = $this->resolveSchema($response, $basePath);
                                    $name   = NameGenerator::getSchemaNameFromSource($response);

                                    $schemas[$name] = $schema;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $schemas;
    }

    private function isName($schema)
    {
        return is_string($schema) && preg_match('/^' . Backend\Schema\Schema::NAME_PATTERN . '$/', $schema);
    }
}
