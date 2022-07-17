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

namespace Fusio\Impl\Service\Generator;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Action;
use Fusio\Impl\Service\Route;
use Fusio\Impl\Service\Schema;
use Fusio\Model\Backend\Action_Create;
use Fusio\Model\Backend\Route_Create;
use Fusio\Model\Backend\Route_Version;
use Fusio\Model\Backend\Schema_Create;
use PSX\Api\Resource;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaTraverser;
use PSX\Schema\Visitor\TypeVisitor;

/**
 * EntityCreator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class EntityCreator
{
    private Route $routeService;
    private Schema $schemaService;
    private Action $actionService;
    private SchemaManagerInterface $schemaManager;

    private array $schemas;
    private array $actions;
    private array $routes;

    public function __construct(Route $routeService, Schema $schemaService, Action $actionService, SchemaManagerInterface $schemaManager)
    {
        $this->routeService = $routeService;
        $this->schemaService = $schemaService;
        $this->actionService = $actionService;
        $this->schemaManager = $schemaManager;

        $this->schemas = [];
        $this->actions = [];
        $this->routes = [];
    }

    public function createSchemas(int $categoryId, array $schemas, UserContext $context): void
    {
        $schema = $this->schemaManager->getSchema(Schema_Create::class);

        foreach ($schemas as $index => $data) {
            $data = \json_decode(\json_encode($data));
            /** @var Schema_Create $record */
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $id = $this->schemaService->exists($record->getName());
            if (!$id) {
                $this->schemaService->create($categoryId, $record, $context);
            }

            $this->schemas[$index] = $record->getName();
        }
    }

    public function createActions(int $categoryId, array $actions, UserContext $context): void
    {
        $schema = $this->schemaManager->getSchema(Action_Create::class);

        foreach ($actions as $index => $data) {
            $data = \json_decode(\json_encode($data));
            /** @var Action_Create $record */
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $id = $this->actionService->exists($record->getName());
            if (!$id) {
                $this->actionService->create($categoryId, $record, $context);
            }

            $this->actions[$index] = $record->getName();
        }
    }

    public function createRoutes(int $categoryId, array $routes, $basePath, $scopes, UserContext $context): void
    {
        $scopes = $scopes ?: [];
        $schema = $this->schemaManager->getSchema(Route_Create::class);

        foreach ($routes as $index => $data) {
            $data = \json_decode(\json_encode($data));
            $this->resolveConfig($data);

            /** @var Route_Create $record */
            $record = (new SchemaTraverser())->traverse($data, $schema, new TypeVisitor());

            $record->setPath($this->buildPath($basePath, $record->getPath()));
            $record->setScopes(array_merge($scopes, $record->getScopes() ?? []));

            foreach ($record->getConfig() as $key => $version) {
                /** @var Route_Version $version */
                $version->setStatus(Resource::STATUS_DEVELOPMENT);
            }

            $id = $this->routeService->exists($record->getPath());
            if (!$id) {
                $id = $this->routeService->create($categoryId, $record, $context);
            }

            $this->routes[$index] = $id;
        }
    }

    private function buildPath(string $basePath, string $path): string
    {
        return '/' . implode('/', array_filter(array_merge(explode('/', $basePath), explode('/', $path))));
    }

    private function resolveConfig(\stdClass $data): void
    {
        $versions = $data->config ?? [];

        foreach ($versions as $index => $version) {
            if (!isset($version->methods) || !$version->methods instanceof \stdClass) {
                continue;
            }

            foreach ($version->methods as $methodName => $method) {
                if (isset($method->parameters)) {
                    $data->config[$index]->methods->{$methodName}->parameters = $this->resolveSchema($method->parameters);
                }

                if (isset($method->request)) {
                    $data->config[$index]->methods->{$methodName}->request = $this->resolveSchema($method->request);
                }

                if (isset($method->responses) && $method->responses instanceof \stdClass) {
                    foreach ($method->responses as $code => $response) {
                        $data->config[$index]->methods->{$methodName}->responses->{$code} = $this->resolveSchema($response);
                    }
                }

                if (isset($method->action)) {
                    $data->config[$index]->methods->{$methodName}->action = $this->resolveAction($method->action);
                }
            }
        }
    }

    private function resolveSchema(int $schema): string
    {
        if ($schema === -1) {
            return 'Passthru';
        }

        if (isset($this->schemas[$schema])) {
            return $this->schemas[$schema];
        } else {
            throw new StatusCode\BadRequestException('Could not resolve schema ' . $schema);
        }
    }

    private function resolveAction(int $action): string
    {
        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        } else {
            throw new StatusCode\BadRequestException('Could not resolve action ' . $action);
        }
    }
}
