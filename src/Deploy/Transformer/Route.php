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

use Fusio\Impl\Deploy\NameGenerator;
use Fusio\Impl\Deploy\TransformerAbstract;
use Fusio\Impl\Service\System\SystemAbstract;
use RuntimeException;

/**
 * Route
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Route extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $routes = isset($data[SystemAbstract::TYPE_ROUTE]) ? $data[SystemAbstract::TYPE_ROUTE] : [];

        if (!empty($routes) && is_array($routes)) {
            $priority = count($routes);
            $result   = [];
            foreach ($routes as $path => $entry) {
                $result[] = $this->transformRoutes($priority, $path, $entry, $basePath);
                $priority--;
            }
            $import->routes = $result;
        }
    }

    protected function transformRoutes($priority, $path, $data, $basePath)
    {
        $data = $this->includeDirective->resolve($data, $basePath, SystemAbstract::TYPE_ROUTE);

        $scopes = [];

        // if we have an indexed array we have a config with multiple versions
        // else we only have a single config
        $config = [];
        if (isset($data[0])) {
            foreach ($data as $row) {
                $config[] = $this->transformRouteConfig($row, $basePath, $scopes);
            }
        } else {
            $config[] = $this->transformRouteConfig($data, $basePath, $scopes);
        }

        return [
            'priority' => $priority,
            'path'     => $path,
            'scopes'   => $scopes,
            'config'   => $config,
        ];
    }

    private function transformRouteConfig(array $row, $basePath, array &$scopes)
    {
        $methods = [];
        if (isset($row['methods']) && is_array($row['methods'])) {
            foreach ($row['methods'] as $method => $config) {
                if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                    throw new RuntimeException('Invalid request method allowed is: GET, POST, PUT, DELETE');
                }

                $methods[$method] = [
                    'active' => isset($config['active']) ? boolval($config['active']) : true,
                    'public' => isset($config['public']) ? boolval($config['public']) : true,
                ];

                if (isset($config['description'])) {
                    $methods[$method]['description'] = $config['description'];
                }

                if (isset($config['parameters'])) {
                    $methods[$method]['parameters'] = NameGenerator::getSchemaNameFromSource($config['parameters']);
                }

                if (isset($config['request'])) {
                    $methods[$method]['request'] = NameGenerator::getSchemaNameFromSource($config['request']);
                } elseif (!in_array($method, ['GET'])) {
                    $methods[$method]['request'] = 'Passthru';
                }

                $responses = [];
                if (isset($config['response'])) {
                    $responses[200] = NameGenerator::getSchemaNameFromSource($config['response']);
                } elseif (isset($config['responses']) && is_array($config['responses'])) {
                    foreach ($config['responses'] as $code => $response) {
                        $responses[intval($code)] = NameGenerator::getSchemaNameFromSource($response);
                    }
                } else {
                    $responses[200] = 'Passthru';
                }

                if (!empty($responses)) {
                    $methods[$method]['responses'] = $responses;
                }

                if (isset($config['action'])) {
                    $methods[$method]['action'] = NameGenerator::getActionNameFromSource($config['action']);
                }
            }
        }

        if (isset($row['scopes'])) {
            $scopes = array_merge($scopes, $row['scopes']);
        }

        return [
            'version' => isset($row['version']) ? $row['version'] : 1,
            'status'  => isset($row['status']) ? $row['status'] : 4,
            'methods' => $methods,
        ];
    }
}
