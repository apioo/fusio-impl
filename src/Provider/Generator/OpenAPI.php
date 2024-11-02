<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Provider\Generator;

use Fusio\Adapter\Http\Action\HttpProcessor;
use Fusio\Adapter\Http\Action\HttpSenderAbstract;
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
use PSX\Api\OperationInterface;
use PSX\Api\Parser;
use PSX\Api\SpecificationInterface;
use PSX\Schema\ContentType;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Generator;
use PSX\Schema\Schema;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaResolver;
use PSX\Schema\Type;
use PSX\Schema\TypeFactory;
use PSX\Schema\TypeInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * OpenAPI
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class OpenAPI implements ProviderInterface
{
    private SchemaManagerInterface $schemaManager;

    public function __construct(SchemaManagerInterface $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function getName(): string
    {
        return 'Import-OpenAPI';
    }

    public function setup(SetupInterface $setup, ParametersInterface $configuration): void
    {
        $baseUrl = '';
        $specification = $this->parse($configuration->get('spec'), $baseUrl);

        // add schemas
        $generator   = new Generator\TypeSchema();
        $definitions = $specification->getDefinitions();

        foreach ($definitions->getTypes() as $name => $type) {
            $schema = new Schema(clone $definitions, $name);
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

        $parser = new Parser\OpenAPI($this->schemaManager);

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
            'type' => HttpSenderAbstract::TYPE_JSON,
        ]));
        $setup->addAction($action);

        $create = new OperationCreate();
        $create->setName($operationId);
        $create->setHttpMethod($operation->getMethod());
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

            $type = $argument->getSchema();
            if ($type instanceof ContentType) {
                continue;
            }

            $data = $type->toArray();

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
        return $this->getRef($operation->getReturn()->getSchema());
    }

    private function getThrows(OperationInterface $operation): ?OperationThrows
    {
        $throws = $operation->getThrows();
        if (empty($throws)) {
            return null;
        }

        $result = new OperationThrows();
        foreach ($throws as $code => $response) {
            $result->put($code, $this->getRef($response->getSchema()));
        }
        return null;
    }

    private function getRef(TypeInterface|ContentType $schema): string
    {
        if ($schema instanceof Type\ReferencePropertyType) {
            return $schema->getTarget() ?? throw new \RuntimeException('No ref provided');
        } elseif ($schema instanceof Type\AnyPropertyType) {
            return SchemaName::PASSTHRU;
        } elseif ($schema instanceof ContentType) {
            return SchemaName::PASSTHRU;
        } else {
            throw new \RuntimeException('Could not resolve return type');
        }
    }
}
