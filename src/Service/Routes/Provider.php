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

namespace Fusio\Impl\Service\Routes;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Factory\ContainerAwareInterface;
use Fusio\Engine\Form;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Parameters;
use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Engine\Routes\Setup;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Action_Create;
use Fusio\Impl\Backend\Model\Route_Create;
use Fusio\Impl\Backend\Model\Route_Method;
use Fusio\Impl\Backend\Model\Route_Method_Responses;
use Fusio\Impl\Backend\Model\Route_Version;
use Fusio\Impl\Backend\Model\Schema_Create;
use Fusio\Impl\Backend\Schema as BackendSchema;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Service\Action;
use Fusio\Impl\Service\Route;
use Fusio\Impl\Service\Schema;
use Psr\Container\ContainerInterface;
use PSX\Api\Resource;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaTraverser;
use PSX\Schema\Visitor\TypeVisitor;

/**
 * Provider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Provider
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var \Fusio\Impl\Provider\ProviderFactory
     */
    private $providerFactory;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \Fusio\Impl\Service\Route
     */
    private $routesService;

    /**
     * @var \Fusio\Impl\Service\Schema
     */
    private $schemaService;

    /**
     * @var \Fusio\Impl\Service\Action
     */
    private $actionService;

    /**
     * @var \Fusio\Engine\Form\ElementFactoryInterface
     */
    private $elementFactory;

    /**
     * @var \PSX\Schema\SchemaManagerInterface
     */
    private $schemaManager;

    /**
     * @var array
     */
    private $schemas;

    /**
     * @var array 
     */
    private $actions;

    /**
     * @var array
     */
    private $routes;

    public function __construct(Connection $connection, ProviderFactory $providerFactory, ContainerInterface $container, Route $routesService, Schema $schemaService, Action $actionService, ElementFactoryInterface $elementFactory, SchemaManagerInterface $schemaManager)
    {
        $this->connection = $connection;
        $this->providerFactory = $providerFactory;
        $this->container = $container;
        $this->routesService = $routesService;
        $this->schemaService = $schemaService;
        $this->actionService = $actionService;
        $this->elementFactory = $elementFactory;
        $this->schemaManager = $schemaManager;

        $this->schemas = [];
        $this->actions = [];
        $this->routes = [];
    }

    public function create($provider, $basePath, $scopes, $config, UserContext $context)
    {
        $provider = $this->providerFactory->factory($provider);

        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        if ($provider instanceof ContainerAwareInterface) {
            $provider->setContainer($this->container);
        }

        $setup = new Setup();
        $configuration = new Parameters($config->getProperties());

        $provider->setup($setup, $basePath, $configuration);

        $this->connection->beginTransaction();

        try {
            $this->createSchemas($setup->getSchemas(), $context);
            $this->createActions($setup->getActions(), $context);
            $this->createRoutes($setup->getRoutes(), $basePath, $scopes, $context);

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    public function getForm(string $providerName)
    {
        $provider = $this->providerFactory->factory($providerName);

        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $builder = new Form\Builder();

        $provider->configure($builder, $this->elementFactory);

        return $builder->getForm();
    }

    public function getChangelog(string $providerName, array $config)
    {
        $provider = $this->providerFactory->factory($providerName);

        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $setup = new Setup();

        $provider->setup($setup, '/[path]', new Parameters($config));

        return [
            'schemas' => $setup->getSchemas(),
            'actions' => $setup->getActions(),
            'routes' => $setup->getRoutes(),
        ];
    }

    private function createSchemas(array $schemas, UserContext $context)
    {
        $schema = $this->schemaManager->getSchema(Schema_Create::class);

        foreach ($schemas as $index => $data) {
            $data = \json_decode(\json_encode($data));
            /** @var Schema_Create $record */
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $id = $this->schemaService->exists($record->getName());
            if (!$id) {
                $this->schemaService->create($record, $context);
            }

            $this->schemas[$index] = $record->getName();
        }
    }

    private function createActions(array $actions, UserContext $context)
    {
        $schema = $this->schemaManager->getSchema(Action_Create::class);

        foreach ($actions as $index => $data) {
            $data = \json_decode(\json_encode($data));
            /** @var Action_Create $record */
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $id = $this->actionService->exists($record->getName());
            if (!$id) {
                $id = $this->actionService->create($record, $context);
            }

            $this->actions[$index] = $id;
        }
    }

    private function createRoutes(array $routes, $basePath, $scopes, UserContext $context)
    {
        $scopes = $scopes ?: [];
        $schema = $this->schemaManager->getSchema(Route_Create::class);

        foreach ($routes as $index => $data) {
            $data = \json_decode(\json_encode($data));
            /** @var Route_Create $record */
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $record->setPath($this->buildPath($basePath, $record->getPath()));
            $record->setScopes(array_merge($scopes, $record->getScopes() ?? []));

            foreach ($record->getConfig() as $key => $version) {
                /** @var Route_Version $version */
                $this->resolveVersion($version);
            }

            $id = $this->routesService->exists($record->getPath());
            if (!$id) {
                $id = $this->routesService->create($record, $context);
            }

            $this->routes[$index] = $id;
        }
    }

    private function buildPath(string $basePath, string $path)
    {
        return '/' . implode('/', array_filter(array_merge(explode('/', $basePath), explode('/', $path))));
    }

    private function resolveVersion(Route_Version $version)
    {
        // we can create only resources in development mode
        $version->setVersion(Resource::STATUS_DEVELOPMENT);

        if ($version->getMethods()) {
            foreach ($version->getMethods() as $methodName => $method) {
                /** @var Route_Method $method */
                $method->setParameters($this->resolveSchema($method->getParameters()));
                $method->setRequest($this->resolveSchema($method->getRequest()));
                $method->setResponse($this->resolveSchema($method->getResponse()));

                if ($method->getResponses()) {
                    $responses = new Route_Method_Responses();
                    foreach ($method->getResponses() as $code => $response) {
                        $responses[$code] = $this->resolveSchema($response);
                    }
                    $method->setResponses($responses);
                }

                $method->setAction($this->resolveAction($method->getAction()));
            }
        }
    }

    private function resolveSchema($schema)
    {
        if (empty($schema)) {
            return null;
        }

        if (isset($this->schemas[$schema])) {
            return $this->schemas[$schema];
        } else {
            throw new StatusCode\BadRequestException('Could not resolve schema ' . $schema);
        }
    }

    private function resolveAction($action)
    {
        if (empty($action)) {
            return null;
        }

        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        } else {
            throw new StatusCode\BadRequestException('Could not resolve action ' . $action);
        }
    }
}
