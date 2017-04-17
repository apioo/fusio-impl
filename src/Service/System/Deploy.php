<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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
        $data   = Yaml::parse($data);
        $import = new \stdClass();

        if (empty($basePath)) {
            $basePath = getcwd();
        }

        foreach ($this->types as $type) {
            if (isset($data[$type]) && is_array($data[$type])) {
                $result = [];
                foreach ($data[$type] as $name => $entry) {
                    $result[] = $this->transform($type, $name, $entry, $basePath);
                }
                $import->{$type} = $result;
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
                    $methods[$method]['request'] = $config['request'];
                }

                if (isset($config['response'])) {
                    $methods[$method]['response'] = $config['response'];
                }

                if (isset($config['action'])) {
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
        } elseif (is_array($data)) {
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
                }

                return $data;
            }
        } elseif (is_array($data)) {
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
}
