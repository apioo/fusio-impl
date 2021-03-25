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

namespace Fusio\Impl\Provider\Routes;

use Fusio\Adapter\Http\Action\HttpProcessor;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Engine\Routes\SetupInterface;

/**
 * OpenAPI
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
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
        if (isset($import->item) && is_array($import->item)) {
            foreach ($import->item as $item) {
                $setup->addRoute(1, $this->normalizePath($item), 'Fusio\Impl\Controller\SchemaApiController', [], [$this->buildConfig($item, $setup)]);
            }
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newTextArea('import', 'Import', 'The Postman JSON export'));
    }

    private function parse(string $import): \stdClass
    {
        $data = json_decode($import);
        if (!$data instanceof \stdClass) {
            throw new \RuntimeException('Provided invalid data');
        }

        return $data;
    }

    private function buildConfig(\stdClass $item, SetupInterface $setup): array
    {
        $methodName = $item->method ?? 'GET';

        return [
            'version' => 1,
            'methods' => [
                $methodName => $this->buildMethod($item, $setup)
            ],
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
            throw new \RuntimeException('no url provided');
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
            $config['request'] = null;
        }

        $config['responses'][200] = null;
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
        if (is_array($path)) {
            return '/' . implode('/', $path);
        } else {
            throw new \RuntimeException('Could not find path');
        }
    }
}
