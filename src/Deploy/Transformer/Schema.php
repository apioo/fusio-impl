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

namespace Fusio\Impl\Deploy\Transformer;

use Fusio\Impl\Backend;
use Fusio\Impl\Deploy\IncludeDirective;
use Fusio\Impl\Deploy\NameGenerator;
use Fusio\Impl\Deploy\TransformerAbstract;
use Fusio\Impl\Service\System\SystemAbstract;
use PSX\Schema\Generator;
use PSX\Schema\Parser;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaResolver;
use RuntimeException;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Schema extends TransformerAbstract
{
    /**
     * @var Parser\TypeSchema\ImportResolver
     */
    private $importResolver;

    public function __construct(IncludeDirective $includeDirective, Parser\TypeSchema\ImportResolver $importResolver)
    {
        parent::__construct($includeDirective);

        $this->importResolver = $importResolver;
    }

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
        if ($data instanceof TaggedValue) {
            if ($data->getTag() === 'include') {
                $file = $basePath . '/' . $data->getValue();

                if (is_file($file)) {
                    return \json_decode($this->resolveFile($file));
                } else {
                    throw new RuntimeException('Could not resolve file: ' . $file);
                }
            } else {
                throw new RuntimeException('Invalid tag provide: ' . $data->getTag());
            }
        } elseif (is_string($data)) {
            if (class_exists($data)) {
                return (object) ['$class' => $data];
            } else {
                return \json_decode($data);
            }
        } elseif (is_array($data) || $data instanceof \stdClass) {
            return $data;
        } else {
            throw new RuntimeException('Schema must be a string or array');
        }
    }

    /**
     * @param string $file
     * @return string
     */
    private function resolveFile(string $file): string
    {
        if (!is_file($file)) {
            throw new RuntimeException('Could not resolve schema ' . $file);
        }

        $basePath  = pathinfo($file, PATHINFO_DIRNAME);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (in_array($extension, ['yaml', 'yml'])) {
            $data = \json_encode(Yaml::parse(file_get_contents($file)));
        } else {
            $data = file_get_contents($file);
        }

        $schema = (new Parser\TypeSchema($this->importResolver, $basePath))->parse($data);

        // remove not needed schemas from the definitions
        (new SchemaResolver())->resolve($schema);

        return (new Generator\TypeSchema())->generate($schema);
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
        $type    = SystemAbstract::TYPE_ROUTE;

        if (isset($data[$type]) && is_array($data[$type])) {
            foreach ($data[$type] as $name => $row) {
                // resolve includes
                $row = $this->includeDirective->resolve($row, $basePath, $type);

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
        return is_string($schema) && preg_match('/^[a-zA-Z0-9\-\_]{3,255}$/', $schema);
    }
}
