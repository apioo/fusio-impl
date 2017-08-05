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

use Fusio\Impl\Service\System\Deploy\IncludeDirective;
use Fusio\Impl\Service\System\Deploy\NameGenerator;
use Fusio\Impl\Service\System\Deploy\TransformerInterface;
use Fusio\Impl\Service\System\SystemAbstract;
use RuntimeException;

/**
 * Routes
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Routes implements TransformerInterface
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $routes = isset($data[SystemAbstract::TYPE_ROUTES]) ? $data[SystemAbstract::TYPE_ROUTES] : [];

        if (!empty($routes) && is_array($routes)) {
            $result = [];
            foreach ($routes as $path => $entry) {
                $result[] = $this->transformRoutes($path, $entry, $basePath);
            }
            $import->routes = $result;
        }
    }

    protected function transformRoutes($path, $data, $basePath)
    {
        $data = IncludeDirective::resolve($data, $basePath, SystemAbstract::TYPE_ROUTES);

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

        return [
            'version' => isset($row['version']) ? $row['version'] : 1,
            'status'  => isset($row['status']) ? $row['status'] : 4,
            'methods' => $methods,
        ];
    }
}
