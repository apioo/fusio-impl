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

use Fusio\Adapter\Http\Action\HttpEngine;
use Fusio\Adapter\Http\Action\HttpProcessor;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Generator\ProviderInterface;
use Fusio\Engine\Generator\Setup;
use Fusio\Engine\Generator\SetupInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Schema\SchemaName;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\OperationCreate;
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
        return 'Import-Insomnia';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration): void
    {
        $import = $this->parse($configuration->get('import'));
        if (!$import instanceof \stdClass) {
            return;
        }

        if (isset($import->resources) && is_array($import->resources)) {
            $env = $this->getEnvironmentVariables($import->resources);

            foreach ($import->resources as $index => $resource) {
                $type = $resource->_type ?? null;
                if ($type !== 'request') {
                    continue;
                }

                $this->buildOperation($resource, $env, $setup, $index);
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

    private function buildOperation(\stdClass $resource, array $env, Setup $setup, string $index): OperationCreate
    {
        $name = $resource->name ?? null;
        if (empty($name)) {
            throw new \RuntimeException('No name provided for resource ' . $index);
        }

        $url = $resource->url ?? null;
        if (empty($url)) {
            throw new \RuntimeException('No url provided for resource ' . $index);
        }

        $path = $this->normalizePath($resource);
        if (empty($path)) {
            throw new \RuntimeException('No path provided for resource ' . $index);
        }

        $method = $resource->method ?? null;
        if (empty($method)) {
            throw new \RuntimeException('No path provided for resource ' . $index);
        }

        foreach ($env as $key => $value) {
            $url = str_replace('{{ ' . $key . ' }}', $value, $url);
        }

        $url  = $this->convertPlaceholderToColon($url);
        $name = $this->buildName([$name, $this->getOperationMethodName($method)]);

        $action = new ActionCreate();
        $action->setName($name);
        $action->setClass(HttpProcessor::class);
        $action->setEngine(PhpClass::class);
        $action->setConfig(ActionConfig::fromArray([
            'url' => $url,
            'type' => HttpEngine::TYPE_JSON,
        ]));
        $setup->addAction($action);

        $operation = new OperationCreate();
        $operation->setName($name);
        $operation->setPublic(!isset($resource->auth));
        $operation->setHttpMethod($method);
        $operation->setHttpPath($path);
        $operation->setHttpCode(200);

        if (isset($resource->body)) {
            $operation->setIncoming(SchemaName::PASSTHRU);
        }

        $operation->setOutgoing(SchemaName::PASSTHRU);
        $operation->setActive($name);
        $setup->addOperation($operation);

        return $operation;
    }

    private function buildName(array $parts, string $separator = '.'): string
    {
        $parts = array_map(function($parts) use ($separator) {
            $parts = array_filter(explode('/', $parts));
            $result = [];
            foreach ($parts as $part) {
                $result[] = preg_replace('/[^0-9A-Za-z_-]/', '_', $part);
            }
            return implode($separator, $result);
        }, $parts);

        return implode($separator, array_filter($parts));
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

    private function getOperationMethodName(string $method): string
    {
        return match ($method) {
            'GET' => 'get',
            'POST' => 'create',
            'PUT' => 'update',
            'PATCH' => 'patch',
            'DELETE' => 'delete',
            default => 'execute',
        };
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
