<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2019 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\Generator\SetupInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Schema\SchemaName;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\OperationCreate;
use Fusio\Model\Backend\OperationParameters;
use Fusio\Model\Backend\OperationSchema;
use Fusio\Model\Backend\OperationThrows;
use Fusio\Model\Backend\SchemaCreate;
use Fusio\Model\Backend\SchemaSource;
use PSX\Api\Operation\ArgumentInterface;
use PSX\Api\Operation\Response;
use PSX\Api\OperationInterface;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Generator;
use PSX\Schema\Schema;
use PSX\Schema\SchemaResolver;
use PSX\Schema\Type;
use PSX\Schema\TypeFactory;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * OpenAPI
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class OpenAPI implements ProviderInterface
{
    private array $schemas = [];

    public function getName(): string
    {
        return 'Import-OpenAPI';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration): void
    {
        $baseUrl = '';
        $specification = $this->parse($configuration->get('spec'), $baseUrl);

        // add schemas
        $generator   = new Generator\TypeSchema();
        $definitions = $specification->getDefinitions();

        $this->schemas = [];

        foreach ($definitions->getTypes(DefinitionsInterface::SELF_NAMESPACE) as $name => $type) {
            $schema = new Schema(TypeFactory::getReference($name), clone $definitions);
            (new SchemaResolver())->resolve($schema);

            $result = (string) $generator->generate($schema);

            $schema = new SchemaCreate();
            $schema->setName($name);
            $schema->setSource(SchemaSource::fromObject(\json_decode($result)));
            $setup->addSchema($schema);
        }

        // add operations and actions
        $operations = $specification->getOperations();
        foreach ($operations->getAll() as $operationId => $operation) {
            $this->buildOperation($operationId, $operation, $setup, $baseUrl);
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newTextArea('spec', 'Specification', 'The OpenAPI specification in the YAML format'));
    }

    private function parse(string $schema, string &$baseUrl): SpecificationInterface
    {
        // check whether we need to transform YAML into JSON
        if (!json_decode($schema)) {
            try {
                $data   = Yaml::parse($schema);
                $schema = json_encode($data);
            } catch (ParseException $e) {
                // invalid YAML syntax
            }
        }

        // get base url
        $data = \json_decode($schema);
        $baseUrl = $data->servers[0]->url ?? '';

        $parser = new \PSX\Api\Parser\OpenAPI();

        return $parser->parse($schema);
    }

    private function buildOperation(string $operationId, OperationInterface $operation, SetupInterface $setup, string $baseUrl): void
    {
        $url = rtrim($baseUrl, '/') . '/' . ltrim($operation->getPath(), '/');

        $action = new ActionCreate();
        $action->setName($operationId);
        $action->setClass(HttpProcessor::class);
        $action->setConfig(ActionConfig::fromArray([
            'url' => $url,
            'type' => HttpEngine::TYPE_JSON,
        ]));
        $setup->addAction($action);

        $create = new OperationCreate();
        $create->setName($operationId);
        $create->setHttpMethod($operation->getMethod());
        $create->setHttpPath($this->normalizePath($operation->getPath()));
        $create->setHttpPath($this->normalizePath($operation->getPath()));
        $create->setHttpCode($operation->getReturn()->getCode());
        $create->setParameters($this->getArguments($operation));
        $create->setIncoming($this->getIncoming($operation));
        $create->setOutgoing($this->getOutgoing($operation));
        $create->setThrows($this->getThrows($operation));
        $create->setAction($operationId);
        $setup->addOperation($create);
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

    private function buildPrefixFromPath($path): string
    {
        return implode('-', array_filter(explode('/', $path)));
    }

    private function normalizePath($path): string
    {
        $path = '/' . implode('/', array_filter(explode('/', $path)));
        $path = preg_replace('/(\{(\w+)\})/i', ':$2', $path);
        return $path;
    }

    private function getArguments(OperationInterface $operation): ?OperationParameters
    {
        if ($operation->getArguments()->isEmpty()) {
            return null;
        }

        $parameters = new OperationParameters();
        foreach ($operation->getArguments() as $name => $argument) {
            /** @var ArgumentInterface $argument */
            if ($argument->getIn() !== ArgumentInterface::IN_QUERY) {
                continue;
            }

            $data = $argument->getSchema()->toArray();

            $schema = new OperationSchema();
            $schema->setType($data['type'] ?? Type::STRING);
            if (isset($data['description'])) {
                $schema->setDescription($data['description']);
            }
            if (isset($data['format'])) {
                $schema->setFormat($data['format']);
            }
            if (isset($data['enum'])) {
                $schema->setEnum($data['enum']);
            }
            $parameters->put($name, $schema);
        }

        return $parameters;
    }

    private function getIncoming(OperationInterface $operation): ?string
    {
        foreach ($operation->getArguments() as $argument) {
            /** @var ArgumentInterface $argument */
            if ($argument->getIn() !== ArgumentInterface::IN_BODY) {
                continue;
            }

            return $this->getRef($argument->getSchema());
        }

        return null;
    }

    private function getOutgoing(OperationInterface $operation): string
    {
        return $this->getRef($operation->getReturn());
    }

    private function getThrows(OperationInterface $operation): ?OperationThrows
    {
        $throws = $operation->getThrows();
        if (empty($throws)) {
            return null;
        }

        $result = new OperationThrows();
        foreach ($throws as $code => $response) {
            $result->put($code, $this->getRef($response));
        }
        return null;
    }

    private function getRef(Response $response): string
    {
        $schema = $response->getSchema();
        if ($schema instanceof Type\ReferenceType) {
            return $schema->getRef();
        } elseif ($schema instanceof Type\AnyType) {
            return SchemaName::PASSTHRU;
        } else {
            throw new \RuntimeException('Could not resolve return type');
        }
    }
}
