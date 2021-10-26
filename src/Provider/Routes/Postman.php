<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * Postman
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Postman implements ProviderInterface
{
    public function getName()
    {
        return 'Postman';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration)
    {
        $import = $this->parse($configuration->get('import'));
        if (!$import instanceof \stdClass) {
            return;
        }

        $resources = [];
        $this->walk($import, $setup, $resources);

        foreach ($resources as $path => $methods) {
            $setup->addRoute(1, $path, SchemaApiController::class, [], [$this->buildConfig($methods, $setup)]);
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
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

            $resources[$path][$method] = $item;
        }
    }

    private function parse(string $import): \stdClass
    {
        $data = json_decode($import);
        if (!$data instanceof \stdClass) {
            throw new \RuntimeException('Provided invalid data');
        }

        return $data;
    }

    private function buildConfig(array $methods, SetupInterface $setup): array
    {
        $result = [];
        foreach ($methods as $methodName => $item) {
            $result[$methodName] = $this->buildMethod($item, $setup);
        }

        return [
            'version' => 1,
            'methods' => $result,
        ];
    }

    private function buildMethod(\stdClass $item, SetupInterface $setup)
    {
        $name = $item->name ?? null;
        if (empty($name)) {
            throw new \RuntimeException('No name provided');
        }

        $url = $item->request->url->raw ?? null;
        if (empty($url)) {
            throw new \RuntimeException('No url provided');
        }

        $name = $this->buildName([$name]);

        $action = $setup->addAction($name, HttpProcessor::class, PhpClass::class, [
            'url' => $url,
        ]);

        $config = [
            'active' => true,
            'public' => !isset($item->auth)
        ];

        if (isset($item->body)) {
            $config['request'] = -1;
        }

        if (isset($item->response) && isset($item->response->code)) {
            $config['responses'][$item->response->code] = -1;
        }

        $config['action'] = $action;

        return $config;
    }

    private function buildName(array $parts)
    {
        $parts = array_map(function($value){
            return preg_replace('/[^0-9A-Za-z_-]/', '_', $value);
        }, $parts);

        return implode('-', array_filter($parts));
    }

    private function normalizePath(\stdClass $item)
    {
        $path = $item->request->url->path ?? null;
        if (empty($path)) {
            throw new \RuntimeException('Could not find path');
        }

        return '/' . Inflection::convertPlaceholderToColon(implode('/', $path));
    }
}
