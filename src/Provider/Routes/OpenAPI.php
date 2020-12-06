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

namespace Fusio\Impl\Provider\Routes;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Engine\Routes\SetupInterface;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Generator;
use PSX\Schema\Schema;
use PSX\Schema\SchemaResolver;
use PSX\Schema\TypeFactory;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * OpenAPI
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class OpenAPI implements ProviderInterface
{
    private $schemas;

    public function getName()
    {
        return 'OpenAPI';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration)
    {
        $specification = $this->parse($configuration->get('spec'));

        // add schemas
        $generator   = new Generator\TypeSchema();
        $definitions = $specification->getDefinitions();

        $this->schemas = [];

        foreach ($definitions->getTypes(DefinitionsInterface::SELF_NAMESPACE) as $name => $type) {
            $schema = new Schema(TypeFactory::getReference($name), clone $definitions);
            (new SchemaResolver())->resolve($schema);

            $result = $generator->generate($schema);

            $this->schemas[$name] = $setup->addSchema($name, \json_decode($result));
        }

        // add routes and actions
        $resources = $specification->getResourceCollection();
        foreach ($resources as $path => $resource) {
            $setup->addRoute(1, $this->normalizePath($resource->getPath()), 'Fusio\Impl\Controller\SchemaApiController', [], [$this->buildConfig($resource, $setup)]);
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newTextArea('spec', 'Specification', 'The OpenAPI specification in the YAML format'));
    }

    private function parse(string $schema): SpecificationInterface
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

        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('PSX\\Schema\\Annotation');
        $parser = new \PSX\Api\Parser\OpenAPI($reader);

        return $parser->parse($schema);
    }

    private function buildConfig(Resource $resource, SetupInterface $setup): array
    {
        $methods = $resource->getMethods();
        $prefix  = $this->buildPrefixFromPath($resource->getPath());

        $config = [
            'version' => 1,
            'methods' => [],
        ];
        
        foreach ($methods as $methodName => $method) {
            $config['methods'][$methodName] = $this->buildMethod($method, $prefix, $setup);
        }

        return $config;
    }

    private function buildMethod(Resource\MethodAbstract $method, $prefix, SetupInterface $setup)
    {
        $statusCode = $this->getSuccessStatusCode($method);
        $name = $this->buildName([$prefix, $method->getOperationId(), $method->getName()]);

        $action = $setup->addAction($name, UtilStaticResponse::class, PhpClass::class, [
            'statusCode' => strval($statusCode ?? 200),
            'response' => json_encode(['message' => 'Test implementation']),
        ]);

        $config = [
            'active' => true,
            'public' => true,
        ];

        if ($method->hasQueryParameters()) {
            $config['parameters'] = $this->schemas[$method->getQueryParameters()] ?? null;
        }

        if ($method->hasRequest()) {
            $config['request'] = $this->schemas[$method->getRequest()] ?? null;
        }

        $config['responses'] = [];
        foreach ($method->getResponses() as $code => $schema) {
            $config['responses'][$code] = $this->schemas[$schema] ?? null;
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

    private function buildPrefixFromPath($path)
    {
        return implode('-', array_filter(explode('/', $path)));
    }

    private function normalizePath($path)
    {
        $path = '/' . implode('/', array_filter(explode('/', $path)));
        $path = preg_replace('/(\{(\w+)\})/i', ':$2', $path);
        return $path;
    }

    private function getSuccessStatusCode(Resource\MethodAbstract $method)
    {
        $statusCode = null;
        foreach ($method->getResponses() as $code => $schema) {
            if ($code >= 200 && $code < 300) {
                $statusCode = $code;
            }
        }

        return $statusCode;
    }
}
