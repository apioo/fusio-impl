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

use Fusio\Impl\Service\System\Deploy\NameGenerator;
use Fusio\Impl\Service\System\Deploy\TransformerInterface;
use Fusio\Impl\Service\System\SystemAbstract;
use PSX\Json\Parser;
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
                    return $this->resolveRefs($file);
                } else {
                    throw new RuntimeException('Could not resolve file: ' . $file);
                }
            } else {
                $data = Parser::decode($data);

                if ($data instanceof \stdClass) {
                    $this->traverseSchema($data, $basePath);
                } else {
                    throw new RuntimeException('JsonSchema must be an object');
                }

                return $data;
            }
        } elseif (is_array($data) || $data instanceof \stdClass) {
            return $data;
        } else {
            throw new RuntimeException('Schema must be a string or array');
        }
    }

    private function resolveRefs($file)
    {
        if (!is_file($file)) {
            throw new RuntimeException('Could not resolve schema ' . $file);
        }

        $basePath = pathinfo($file, PATHINFO_DIRNAME);
        $data     = Parser::decode(file_get_contents($file));

        if ($data instanceof \stdClass) {
            $this->traverseSchema($data, $basePath);
        }

        return $data;
    }

    private function traverseSchema(\stdClass $data, $basePath)
    {
        $props = get_object_vars($data);
        foreach ($props as $key => $value) {
            if ($data->{$key} instanceof \stdClass) {
                if (isset($data->{$key}->{'$ref'}) && is_string($data->{$key}->{'$ref'})) {
                    $uri = new Uri($data->{$key}->{'$ref'});

                    if ($uri->isAbsolute()) {
                        if ($uri->getScheme() == 'file') {
                            $data->{$key} = $this->resolveRefs($basePath . '/' . $uri->getPath());
                        } else {
                            throw new RuntimeException('Scheme ' . $uri->getScheme() . ' is not supported');
                        }
                    }
                } else {
                    $this->traverseSchema($data->{$key}, $basePath);
                }
            }
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
                if (isset($row['methods']) && is_array($row['methods'])) {
                    foreach ($row['methods'] as $method => $config) {
                        if (isset($config['request']) && !preg_match('/' . NameGenerator::NAME_REGEXP . '/', $config['request'])) {
                            $schema = $this->resolveSchema($config['request'], $basePath);
                            $name   = NameGenerator::getSchemaNameFromSource($config['request']);

                            $schemas[$name] = $schema;
                        }

                        if (isset($config['response']) && !preg_match('/' . NameGenerator::NAME_REGEXP . '/', $config['response'])) {
                            $schema = $this->resolveSchema($config['response'], $basePath);
                            $name   = NameGenerator::getSchemaNameFromSource($config['response']);

                            $schemas[$name] = $schema;
                        }
                    }
                }
            }
        }

        return $schemas;
    }
}
