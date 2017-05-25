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

namespace Fusio\Impl\Service\System;

use PSX\Json;
use PSX\Uri\Uri;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * The deploy service uses the import service to insert the data into the 
 * system. In general it simply converts the yaml format
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Deploy
{
    /**
     * @var \Fusio\Impl\Service\System\Import
     */
    protected $importService;

    /**
     * @var \Fusio\Impl\Service\System\Migration
     */
    protected $migrationService;

    /**
     * @var array
     */
    protected $types = [SystemAbstract::TYPE_CONNECTION, SystemAbstract::TYPE_SCHEMA, SystemAbstract::TYPE_ACTION, SystemAbstract::TYPE_ROUTES];

    /**
     * @var string
     */
    private $nameRegexp = '^[A-z0-9\-\_]{3,64}$';

    /**
     * @param \Fusio\Impl\Service\System\Import $importService
     * @param \Fusio\Impl\Service\System\Migration $migrationService
     */
    public function __construct(Import $importService, Migration $migrationService)
    {
        $this->importService    = $importService;
        $this->migrationService = $migrationService;
    }

    public function deploy($data, $basePath = null)
    {
        $data   = Yaml::parse($this->replaceProperties($data));
        $import = new \stdClass();

        if (empty($basePath)) {
            $basePath = getcwd();
        }

        $actions = $this->resolveActionsFromRoutes($data);
        $schemas = $this->resolveSchemasFromRoutes($data, $basePath);

        foreach ($this->types as $type) {
            if (isset($data[$type]) && is_array($data[$type])) {
                $result = [];
                foreach ($data[$type] as $name => $entry) {
                    $result[] = $this->transform($type, $name, $entry, $basePath);
                }
                $import->{$type} = $result;
            }
        }

        // append schemas which we have automatically created
        if (!empty($schemas)) {
            if (!isset($import->schema)) {
                $import->schema = [];
            }

            foreach ($schemas as $name => $entry) {
                $import->schema[] = $this->transform(SystemAbstract::TYPE_SCHEMA, $name, $entry, $basePath);
            }
        }

        // append actions which we have automatically created
        if (!empty($actions)) {
            if (!isset($import->action)) {
                $import->action = [];
            }

            foreach ($actions as $name => $entry) {
                $import->action[] = $this->transform(SystemAbstract::TYPE_ACTION, $name, $entry, $basePath);
            }
        }

        // import definition
        $log = $this->importService->import(json_encode($import));

        // handle migration
        $log = array_merge($log, $this->migrationService->execute($data, $basePath));

        return $log;
    }

    protected function transform($type, $name, $data, $basePath)
    {
        switch ($type) {
            case SystemAbstract::TYPE_CONNECTION:
                return $this->transformConnection($name, $data, $basePath);
                break;

            case SystemAbstract::TYPE_SCHEMA:
                return $this->transformSchema($name, $data, $basePath);
                break;

            case SystemAbstract::TYPE_ACTION:
                return $this->transformAction($name, $data, $basePath);
                break;

            case SystemAbstract::TYPE_ROUTES:
                return $this->transformRoutes($name, $data, $basePath);
                break;

            default:
                throw new RuntimeException('Invalid type');
        }
    }

    protected function transformConnection($name, $data, $basePath)
    {
        $data = $this->resolveResource($data, $basePath, SystemAbstract::TYPE_CONNECTION);
        $data['name'] = $name;

        return $data;
    }

    protected function transformSchema($name, $data, $basePath)
    {
        return [
            'name'   => $name,
            'source' => $this->resolveSchema($data, $basePath),
        ];
    }

    protected function transformAction($name, $data, $basePath)
    {
        $data = $this->resolveResource($data, $basePath, SystemAbstract::TYPE_ACTION);
        $data['name'] = $name;

        return $data;
    }

    protected function transformRoutes($path, $data, $basePath)
    {
        $data = $this->resolveResource($data, $basePath, SystemAbstract::TYPE_ROUTES);

        // if we have an indexed array we have a list of configs else we
        // only have a single config
        $config = [];
        if (isset($data[0])) {
            foreach ($data as $row) {
                $config[] = $this->transformRouteConfig($row, $basePath);
            }
        } else {
            $config[] = $this->transformRouteConfig($data, $basePath);
        }

        return [
            'path'   => $path,
            'config' => $config,
        ];
    }

    private function transformRouteConfig(array $row, $basePath)
    {
        $methods = [];
        if (isset($row['methods']) && is_array($row['methods'])) {
            foreach ($row['methods'] as $method => $config) {
                if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
                    throw new RuntimeException('Invalid request method allowed is: GET, POST, PUT, DELETE');
                }

                $methods[$method] = [
                    'active' => isset($config['active']) ? boolval($config['active']) : true,
                    'public' => isset($config['public']) ? boolval($config['public']) : true,
                ];

                if (isset($config['request'])) {
                    if (preg_match('/' . $this->nameRegexp . '/', $config['request'])) {
                        $methods[$method]['request'] = $config['request'];
                    } else {
                        $methods[$method]['request'] = $this->getSchemaNameFromSource($config['request']);
                    }
                } elseif (!in_array($method, ['GET'])) {
                    $methods[$method]['request'] = 'Passthru';
                }

                if (isset($config['response'])) {
                    if (preg_match('/' . $this->nameRegexp . '/', $config['response'])) {
                        $methods[$method]['response'] = $config['response'];
                    } else {
                        $methods[$method]['response'] = $this->getSchemaNameFromSource($config['response']);
                    }
                } else {
                    $methods[$method]['response'] = 'Passthru';
                }

                if (isset($config['action'])) {
                    // in case the action contains a class we have automatically
                    // created an action
                    if (strpos($config['action'], '\\') !== false && class_exists($config['action'])) {
                        $config['action'] = $this->getActionNameFromClass($config['action']);
                    }

                    $methods[$method]['action'] = $config['action'];
                }
            }
        }

        return [
            'version' => isset($row['version']) ? $row['version'] : 1,
            'status'  => isset($row['status']) ? $row['status'] : 4,
            'methods' => $methods,
        ];
    }

    /**
     * In case the routes contains a class as action we automatically create a
     * fitting action entry
     * 
     * @param array $data
     * @return array
     */
    private function resolveActionsFromRoutes(array $data)
    {
        $actions = [];
        $type    = SystemAbstract::TYPE_ROUTES;

        if (isset($data[$type]) && is_array($data[$type])) {
            foreach ($data[$type] as $name => $row) {
                if (isset($row['methods']) && is_array($row['methods'])) {
                    foreach ($row['methods'] as $method => $config) {
                        if (isset($config['action']) && strpos($config['action'], '\\') !== false) {
                            if (class_exists($config['action'])) {
                                $name = $this->getActionNameFromClass($config['action']);

                                $actions[$name] = [
                                    'class' => $config['action']
                                ];
                            } else {
                                throw new RuntimeException('Provided class ' . $config['action'] . ' does not exist');
                            }
                        }
                    }
                }
            }
        }

        return $actions;
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
                        if (isset($config['request']) && !preg_match('/' . $this->nameRegexp . '/', $config['request'])) {
                            $schema = $this->resolveSchema($config['request'], $basePath);
                            $name   = $this->getSchemaNameFromSource($config['request']);

                            $schemas[$name] = $schema;
                        }

                        if (isset($config['response']) && !preg_match('/' . $this->nameRegexp . '/', $config['response'])) {
                            $schema = $this->resolveSchema($config['response'], $basePath);
                            $name   = $this->getSchemaNameFromSource($config['response']);

                            $schemas[$name] = $schema;
                        }
                    }
                }
            }
        }

        return $schemas;
    }

    private function resolveResource($data, $basePath, $type)
    {
        if (is_string($data)) {
            if (substr($data, 0, 8) == '!include') {
                $file = $basePath . '/' . substr($data, 9);

                if (is_file($file)) {
                    return Yaml::parse(file_get_contents($file));
                } else {
                    throw new RuntimeException('Could not resolve file: ' . $file);
                }
            }

            return $data;
        } elseif (is_array($data) || $data instanceof \stdClass) {
            return $data;
        } else {
            throw new RuntimeException(ucfirst($type) . ' must be either a string containing an "!include" directive or array');
        }
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
                $data = Json\Parser::decode($data);

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
        $data     = Json\Parser::decode(file_get_contents($file));

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

    private function replaceProperties($data)
    {
        $vars = [];
        
        // dir properties
        $vars['dir'] = [
            'cache' => PSX_PATH_CACHE,
            'src'   => PSX_PATH_LIBRARY,
            'temp'  => sys_get_temp_dir(),
        ];

        // env properties
        $vars['env'] = [];
        foreach ($_SERVER as $key => $value) {
            if (is_scalar($value)) {
                $vars['env'][strtolower($key)] = $value;
            }
        }

        foreach ($vars as $type => $properties) {
            $search  = [];
            $replace = [];
            foreach ($properties as $key => $value) {
                $search[]  = '${' . $type . '.' . $key . '}';
                $replace[] = is_string($value) ? trim(json_encode($value), '"') : $value;
            }

            $data = str_replace($search, $replace, $data);

            // check whether we have variables which were not replaced
            preg_match('/\$\{' . $type . '\.([0-9A-z_]+)\}/', $data, $matches);
            if (isset($matches[0])) {
                throw new \RuntimeException('Usage of unknown property ' . $matches[0]);
            }
        }

        return $data;
    }

    private function getActionNameFromClass($class)
    {
        $name = str_replace('\\', '', $class);
        $name = str_replace('FusioCustomAction', '', $name);

        return $name;
    }

    private function getSchemaNameFromSource($source)
    {
        if (is_string($source)) {
            if (substr($source, 0, 8) == '!include') {
                $name = trim(substr($source, 9));
                $name = str_replace('\\', '/', $name);
                $name = str_replace('resources/schema/', '', $name);
                $name = str_replace('.json', '', $name);
                $name = str_replace(' ', '', ucwords(str_replace('/', ' ', $name)));

                return $name;
            }

            return 'Schema-' . substr(md5($source), 0, 8);
        } elseif (is_array($source)) {
            return 'Schema-' . substr(md5(json_encode($source)), 0, 8);
        } else {
            throw new RuntimeException('Schema should be a string containing an "!include" directive pointing to a JsonSchema file');
        }
    }
}
