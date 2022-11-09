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
use Fusio\Model;
use Fusio\Model\Backend\RouteMethod;
use Fusio\Model\Backend\RouteMethodResponses;
use Fusio\Model\Backend\RouteVersion;
use PSX\Api\Resource;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Parser\TypeSchema;

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

    private array $schemas;
    private array $actions;
    private array $routes;

    public function __construct(Route $routeService, Schema $schemaService, Action $actionService)
    {
        $this->routeService = $routeService;
        $this->schemaService = $schemaService;
        $this->actionService = $actionService;

        $this->schemas = [];
        $this->actions = [];
        $this->routes = [];
    }

    /**
     * @param Model\Backend\SchemaCreate[] $schemas
     */
    public function createSchemas(int $categoryId, array $schemas, string $prefix, UserContext $context): void
    {
        foreach ($schemas as $index => $record) {
            $record->setName($this->buildName($prefix, $record->getName() ?? ''));

            $source = $record->getSource();
            if (isset($source['$import']) && is_iterable($source['$import'])) {
                $import = [];
                foreach ($source['$import'] as $name => $schema) {
                    if (str_starts_with($schema, 'schema:///')) {
                        $import[$name] = 'schema:///' . $prefix . '_' . substr($schema, 10);
                    } else {
                        $import[$name] = $schema;
                    }
                }
                $source['$import'] = $import;
                $record->setSource($source);
            }

            $id = $this->schemaService->exists($record->getName() ?? '');
            if (!$id) {
                $this->schemaService->create($categoryId, $record, $context);
            }

            $this->schemas[$index] = $record->getName();
        }
    }

    /**
     * @param Model\Backend\ActionCreate[] $actions
     */
    public function createActions(int $categoryId, array $actions, string $prefix, UserContext $context): void
    {
        foreach ($actions as $index => $record) {
            $record->setName($this->buildName($prefix, $record->getName() ?? ''));

            $id = $this->actionService->exists($record->getName() ?? '');
            if (!$id) {
                $this->actionService->create($categoryId, $record, $context);
            }

            $this->actions[$index] = $record->getName();
        }
    }

    /**
     * @param Model\Backend\RouteCreate[] $routes
     */
    public function createRoutes(int $categoryId, array $routes, $basePath, $scopes, ?bool $public, UserContext $context): void
    {
        $scopes = $scopes ?: [];

        foreach ($routes as $index => $record) {
            $record->setPath($this->buildPath($basePath, $record->getPath() ?? ''));
            $record->setScopes(array_unique(array_merge($scopes, $record->getScopes() ?? [])));

            $config = $record->getConfig() ?? [];
            foreach ($config as $version) {
                /** @var RouteVersion $version */
                $version->setVersion(1);
                $version->setStatus(Resource::STATUS_DEVELOPMENT);
                $methods = $version->getMethods() ?? [];
                foreach ($methods as $method) {
                    /** @var RouteMethod $method */
                    $method->setActive(true);
                    $method->setPublic($public === true);

                    $parameters = $method->getParameters();
                    if ($parameters !== null) {
                        $method->setParameters($this->resolveSchema((int) $parameters));
                    }

                    $request = $method->getRequest();
                    if ($request !== null) {
                        $method->setRequest($this->resolveSchema((int) $request));
                    }

                    $responses = $method->getResponses();
                    if ($responses instanceof RouteMethodResponses) {
                        foreach ($responses as $statusCode => $response) {
                            if ($response !== null) {
                                $responses[$statusCode] = $this->resolveSchema((int) $response);
                            }
                        }
                    }

                    $action = $method->getAction();
                    if ($action !== null) {
                        $method->setAction($this->resolveAction((int) $action));
                    }
                }
            }

            $id = $this->routeService->exists((string) $record->getPath());
            if (!$id) {
                $id = $this->routeService->create($categoryId, $record, $context);
            }

            $this->routes[$index] = $id;
        }
    }

    private function buildPath(string $basePath, string $path): string
    {
        $parts = array_merge(explode('/', $basePath), explode('/', $path));
        $parts = array_filter($parts, function ($value) {
            return $value !== '';
        });
        return '/' . implode('/', $parts);
    }

    private function buildName(string $prefix, string $name): string
    {
        $parts = explode('_', $prefix . '_' . $name);
        $parts = array_filter($parts, function ($value) {
            return $value !== '';
        });
        $parts = array_map('ucfirst', $parts);
        $parts = implode('_', $parts);
        return $parts;
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
