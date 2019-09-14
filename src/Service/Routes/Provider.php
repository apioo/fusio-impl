<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\Form;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Parameters;
use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Engine\Routes\Setup;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Schema as BackendSchema;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Service\Action;
use Fusio\Impl\Service\Routes;
use Fusio\Impl\Service\Schema;
use PSX\Api\Resource;
use PSX\Http\Exception as StatusCode;
use PSX\Record\RecordInterface;
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
     * @var \Fusio\Impl\Service\Routes
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

    public function __construct(Connection $connection, ProviderFactory $providerFactory, Routes $routesService, Schema $schemaService, Action $actionService, ElementFactoryInterface $elementFactory, SchemaManagerInterface $schemaManager)
    {
        $this->connection = $connection;
        $this->providerFactory = $providerFactory;
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
        $schema = $this->schemaManager->getSchema(BackendSchema\Schema\Create::class);

        foreach ($schemas as $index => $data) {
            $data   = \json_decode(\json_encode($data));
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $id = $this->schemaService->exists($record->name);
            if (!$id) {
                $id = $this->schemaService->create(
                    $record->name,
                    $record->source,
                    $context
                );
            }

            $this->schemas[$index] = $id;
        }
    }

    private function createActions(array $actions, UserContext $context)
    {
        $schema = $this->schemaManager->getSchema(BackendSchema\Action\Create::class);

        foreach ($actions as $index => $data) {
            $data   = \json_decode(\json_encode($data));
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $id = $this->actionService->exists($record->name);
            if (!$id) {
                $id = $this->actionService->create(
                    $record->name,
                    $record->class,
                    $record->engine,
                    $record->config ? $record->config->getProperties() : null,
                    $context
                );
            }

            $this->actions[$index] = $id;
        }
    }

    private function createRoutes(array $routes, $basePath, $scopes, UserContext $context)
    {
        $scopes = $scopes ?: [];
        $schema = $this->schemaManager->getSchema(BackendSchema\Routes\Create::class);

        foreach ($routes as $index => $data) {
            $data   = \json_decode(\json_encode($data));
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $record->path = $this->buildPath($basePath, $record->path);
            $record->config = $this->buildConfig($record->config);

            if (is_array($record->scopes)) {
                $scopes = array_merge($scopes, $record->scopes);
            }

            $id = $this->routesService->exists($record->path);
            if (!$id) {
                $id = $this->routesService->create(
                    $record->priority,
                    $record->path,
                    $record->controller,
                    $scopes,
                    $record->config,
                    $context
                );
            }

            $this->routes[$index] = $id;
        }
    }

    private function buildPath($basePath, $path)
    {
        return '/' . implode('/', array_filter(array_merge(explode('/', $basePath), explode('/', $path))));
    }

    private function buildConfig($config)
    {
        if (!is_iterable($config)) {
            throw new StatusCode\BadRequestException('Config must be an array');
        }

        foreach ($config as $key => $version) {
            // we can create only resources in development mode
            $config[$key]['status'] = Resource::STATUS_DEVELOPMENT;

            if (isset($version['methods']) && is_iterable($version['methods'])) {
                foreach ($version['methods'] as $methodName => $method) {
                    if (isset($method['parameters'])) {
                        $config[$key]['methods'][$methodName]['parameters'] = $this->resolveSchema($method['parameters']);
                    }

                    if (isset($method['request'])) {
                        $config[$key]['methods'][$methodName]['request'] = $this->resolveSchema($method['request']);
                    }

                    if (isset($method['response'])) {
                        $config[$key]['methods'][$methodName]['response'] = $this->resolveSchema($method['response']);
                    }

                    if (isset($method['responses']) && is_iterable($method['responses'])) {
                        $responses = [];
                        foreach ($method['responses'] as $code => $response) {
                            $responses[$code] = $this->resolveSchema($response);
                        }

                        $config[$key]['methods'][$methodName]['responses'] = $responses;
                    }

                    if (isset($method['action'])) {
                        $config[$key]['methods'][$methodName]['action'] = $this->resolveAction($method['action']);
                    }
                }
            }
        }

        return $config;
    }

    private function resolveSchema($schema)
    {
        if (isset($this->schemas[$schema])) {
            return $this->schemas[$schema];
        } else {
            throw new StatusCode\BadRequestException('Could not resolve schema ' . $schema);
        }
    }

    private function resolveAction($action)
    {
        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        } else {
            throw new StatusCode\BadRequestException('Could not resolve action ' . $action);
        }
    }
}
