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

namespace Fusio\Impl\Provider\Routes;

use Fusio\Adapter\Http\Action\HttpEngine;
use Fusio\Adapter\Http\Action\HttpProcessor;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Engine\Routes\SetupInterface;
use Fusio\Impl\Controller\SchemaApiController;
use PSX\Api\Util\Inflection;

/**
 * Insomnia
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Insomnia implements ProviderInterface
{
    public function getName(): string
    {
        return 'Insomnia';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration): void
    {
        $import = $this->parse($configuration->get('import'));
        if (!$import instanceof \stdClass) {
            return;
        }

        if (isset($import->resources) && is_array($import->resources)) {
            $env = $this->getEnvironmentVariables($import->resources);


            $resources = [];
            foreach ($import->resources as $resource) {
                $type = $resource->_type ?? null;
                if ($type !== 'request') {
                    continue;
                }

                $path = $this->normalizePath($resource);
                if (!isset($resources[$path])) {
                    $resources[$path] = [];
                }

                $method = $resource->method ?? null;

                $resources[$path][$method] = $resource;
            }

            foreach ($resources as $path => $methods) {
                $setup->addRoute(1, $path, SchemaApiController::class, [], [$this->buildConfig($methods, $setup, $env)]);
            }
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newTextArea('import', 'Import', 'The Insomnia JSON export'));
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
        foreach ($methods as $methodName => $resource) {
            $result[$methodName] = $this->buildMethod($methodName, $resource, $setup, $env);
        }

        return [
            'version' => 1,
            'methods' => $result,
        ];
    }

    private function buildMethod(string $methodName, \stdClass $resource, SetupInterface $setup, array $env): array
    {
        $name = $resource->name ?? null;
        if (empty($name)) {
            throw new \RuntimeException('No name provided');
        }

        $url = $resource->url ?? null;
        if (empty($url)) {
            throw new \RuntimeException('No url provided');
        }

        foreach ($env as $key => $value) {
            $url = str_replace('{{ ' . $key . ' }}', $value, $url);
        }

        $url = $this->convertPlaceholderToColon($url);

        $name = $this->buildName([$methodName, $name]);

        $action = $setup->addAction($name, HttpProcessor::class, PhpClass::class, [
            'url' => $url,
            'type' => HttpEngine::TYPE_JSON,
        ]);

        $config = [
            'active' => true,
            'public' => !isset($resource->auth)
        ];

        if (isset($resource->body)) {
            $config['request'] = -1;
        }

        $config['responses'][200] = -1;
        $config['action'] = $action;

        return $config;
    }

    private function buildName(array $parts): string
    {
        $parts = array_map(function($value){
            return preg_replace('/[^0-9A-Za-z_-]/', '_', $value);
        }, $parts);

        return implode('-', array_filter($parts));
    }

    private function normalizePath(\stdClass $resource): string
    {
        $path = $resource->name ?? null;
        if (empty($path)) {
            throw new \RuntimeException('Could not find path');
        }

        return Inflection::convertPlaceholderToColon($path);
    }

    private function convertPlaceholderToColon(string $path)
    {
        $path = preg_replace('/(\{\{ (\w+) \}\})/i', ':$2', $path);

        return $path;
    }

    private function getEnvironmentVariables(array $resources): array
    {
        $env = [];
        $sort = [];
        foreach ($resources as $resource) {
            $type = $resource->_type ?? null;
            if ($type !== 'environment') {
                continue;
            }

            if ($resource->data instanceof \stdClass) {
                $data = [];
                foreach ($resource->data as $key => $value) {
                    $data[$key] = $value;
                }

                $env[] = $data;
                $sort[] = $resource->metaSortKey;
            }
        }

        array_multisort($env, $sort);
        rsort($env);

        $result = [];
        foreach ($env as $data) {
            foreach ($data as $key => $value) {
                $result[$key] = $this->substituteVars($result, $value);
            }
        }

        return $result;
    }

    private function substituteVars(array $env, $content)
    {
        foreach ($env as $key => $value) {
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }

        return $content;
    }
}
