<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Provider\Generator;

use Fusio\Adapter\Http\Action\HttpProcessor;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Generator\ProviderInterface;
use Fusio\Engine\Generator\SetupInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Impl\Controller\SchemaApiController;
use PSX\Api\Util\Inflection;

/**
 * Postman
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Postman implements ProviderInterface
{
    public function getName(): string
    {
        return 'Import-Postman';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration): void
    {
        $import = $this->parse($configuration->get('import'));
        if (!$import instanceof \stdClass) {
            return;
        }

        $env = [];
        if (isset($import->variable) && is_array($import->variable)) {
            $env = $this->getEnv($import->variable);
        }

        $resources = [];
        $this->walk($import, $setup, $resources);

        foreach ($resources as $path => $methods) {
            $setup->addRoute(1, $path, SchemaApiController::class, [], [$this->buildConfig($methods, $setup, $env)]);
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newTextArea('import', 'Import', 'The Postman JSON export'));
    }

    private function walk(\stdClass $item, SetupInterface $setup, array &$resources)
    {
        if (isset($item->item) && is_array($item->item)) {
            foreach ($item->item as $child) {
                $this->walk($child, $setup, $resources);
            }
        }

        if (isset($item->request) && $item->request instanceof \stdClass) {
            $path = $this->normalizePath($item);
            if (!isset($resources[$path])) {
                $resources[$path] = [];
            }

            $method = $item->request->method ?? null;

            if (!empty($method)) {
                $resources[$path][$method] = $item;
            }
        }
    }

    private function getEnv(array $variables): array
    {
        $result = [];
        foreach ($variables as $variable) {
            if (isset($variable->key) && isset($variable->value)) {
                $result[$variable->key] = $variable->value;
            }
        }
        return $result;
    }

    private function parse(string $import): \stdClass
    {
        $data = json_decode($import);
        if (!$data instanceof \stdClass) {
            throw new \RuntimeException('Provided invalid data');
        }

        return $data;
    }

    private function buildConfig(array $methods, SetupInterface $setup, array $env): array
    {
        $result = [];
        foreach ($methods as $methodName => $item) {
            $methodName = strtoupper($methodName);
            if (!in_array($methodName, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                continue;
            }

            $result[$methodName] = $this->buildMethod($item, $setup, $env, $methodName);
        }

        return [
            'version' => 1,
            'methods' => $result,
        ];
    }

    private function buildMethod(\stdClass $item, SetupInterface $setup, array $env, string $methodName): array
    {
        $name = $item->name ?? null;
        if (empty($name)) {
            throw new \RuntimeException('No name provided');
        }

        if (strlen($name) > 58) {
            $name = substr($name, 0, 58);
        }

        $host = $item->request->url->host ?? null;
        $path = $item->request->url->path ?? null;

        if (is_array($host)) {
            $host = implode('.', $host);
        }

        if (!empty($host) && is_array($path)) {
            $url = rtrim($host, '/') . '/' . implode('/', $path);
        } else {
            throw new \RuntimeException('No url provided for ' . $item->name);
        }

        foreach ($env as $key => $value) {
            if ($key === 'baseUrl') {
                $url = str_replace('{{' . $key . '}}', rtrim($value, '/'), $url);
            } else {
                $url = str_replace('{{' . $key . '}}', $value, $url);
            }
        }

        $query = $item->request->url->query ?? null;
        $parameters = null;
        if (!empty($query) && is_array($query)) {
            $schemaName = $this->buildName([$name, 'Query']);
            $parameters = $setup->addSchema($schemaName, $this->getQuerySchema($query, $schemaName));
        }

        $name = $this->buildName([$name, $methodName]);

        $action = $setup->addAction($name, HttpProcessor::class, PhpClass::class, [
            'url' => $url,
        ]);

        $config = [
            'active' => true,
            'public' => !isset($item->auth)
        ];

        if (!empty($parameters)) {
            $config['parameters'] = $parameters;
        }

        if (isset($item->body)) {
            $config['request'] = -1;
        }

        if (isset($item->response) && isset($item->response->code)) {
            $config['responses'][$item->response->code] = -1;
        }

        $config['action'] = $action;

        return $config;
    }

    private function buildName(array $parts): string
    {
        $parts = array_map(function($value){
            return preg_replace('/[^0-9A-Za-z_-]/', '_', $value);
        }, $parts);

        return implode('_', array_filter($parts));
    }

    private function normalizePath(\stdClass $item): string
    {
        $path = $item->request->url->path ?? null;
        if (empty($path)) {
            throw new \RuntimeException('Could not find path');
        }

        return '/' . Inflection::convertPlaceholderToColon(implode('/', $path));
    }

    private function getQuerySchema(array $query, string $schemaName): array
    {
        $properties = [];
        foreach ($query as $parameter) {
            if (!isset($parameter->key)) {
                continue;
            }

            $type = [
                'type' => 'string'
            ];

            if (isset($parameter->description)) {
                $type['description'] = $parameter->description;
            }

            $properties[$parameter->key] = $type;
        }

        return [
            'definitions' => [
                $schemaName => [
                    'type' => 'object',
                    'properties' => $properties
                ]
            ],
            '$ref' => $schemaName,
        ];
    }
}
