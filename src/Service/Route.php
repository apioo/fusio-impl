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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Event\Route\CreatedEvent;
use Fusio\Impl\Event\Route\DeletedEvent;
use Fusio\Impl\Event\Route\UpdatedEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\RouteCreate;
use Fusio\Model\Backend\RouteUpdate;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Route
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Route
{
    private Table\Route $routesTable;
    private Table\Route\Method $methodTable;
    private Service\Scope $scopeService;
    private Route\Config $configService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Route $routesTable, Table\Route\Method $methodTable, Service\Scope $scopeService, Route\Config $configService, EventDispatcherInterface $eventDispatcher)
    {
        $this->routesTable     = $routesTable;
        $this->methodTable     = $methodTable;
        $this->scopeService    = $scopeService;
        $this->configService   = $configService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, RouteCreate $route, UserContext $context): int
    {
        $path = $route->getPath();
        if (empty($path)) {
            throw new StatusCode\BadRequestException('Path not provided');
        }

        Route\Validator::assertPath($path);

        $config = $route->getConfig();
        if (empty($config)) {
            throw new StatusCode\BadRequestException('Config not provided');
        }

        // check whether route exists
        if ($this->exists($path)) {
            throw new StatusCode\BadRequestException('Route already exists');
        }

        if ($route->getPriority() === null) {
            $route->setPriority($this->routesTable->getMaxPriority() + 1);
        } elseif ($route->getPriority()>= 0x1000000) {
            throw new StatusCode\GoneException('Priority can not be greater or equal to ' . 0x1000000);
        }

        $controller = $route->getController();
        if (!empty($controller)) {
            if (!class_exists($controller)) {
                throw new StatusCode\BadRequestException('Provided controller does not exist');
            }
        } else {
            $controller = SchemaApiController::class;
        }

        // create route
        try {
            $this->routesTable->beginTransaction();

            $record = new Table\Generated\RoutesRow([
                Table\Generated\RoutesTable::COLUMN_CATEGORY_ID => $categoryId,
                Table\Generated\RoutesTable::COLUMN_STATUS => Table\Route::STATUS_ACTIVE,
                Table\Generated\RoutesTable::COLUMN_PRIORITY => $route->getPriority(),
                Table\Generated\RoutesTable::COLUMN_METHODS => 'ANY',
                Table\Generated\RoutesTable::COLUMN_PATH => $path,
                Table\Generated\RoutesTable::COLUMN_CONTROLLER => $controller,
                Table\Generated\RoutesTable::COLUMN_METADATA => $route->getMetadata() !== null ? json_encode($route->getMetadata()) : null,
            ]);

            $this->routesTable->create($record);

            $routeId = $this->routesTable->getLastInsertId();
            $route->setId($routeId);

            // assign scopes
            $scopes = $route->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createFromRoute($categoryId, $routeId, $scopes, $context);
            }

            // handle config
            $this->configService->handleConfig($categoryId, $routeId, $path, $config, $context);

            $this->routesTable->commit();
        } catch (\Throwable $e) {
            $this->routesTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($route, $context));

        return $routeId;
    }

    public function update(int $routeId, RouteUpdate $route, UserContext $context): int
    {
        $existing = $this->routesTable->find($routeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find route');
        }

        if ($existing->getStatus() == Table\Route::STATUS_DELETED) {
            throw new StatusCode\GoneException('Route was deleted');
        }

        $config = $route->getConfig();
        if (empty($config)) {
            throw new StatusCode\BadRequestException('Config not provided');
        }

        $priority = $route->getPriority();
        if ($priority === null) {
            $priority = $this->routesTable->getMaxPriority() + 1;
        } elseif ($priority >= 0x1000000) {
            throw new StatusCode\GoneException('Priority can not be greater or equal to ' . 0x1000000);
        }

        try {
            $this->routesTable->beginTransaction();

            // update route
            $record = new Table\Generated\RoutesRow([
                Table\Generated\RoutesTable::COLUMN_ID => $existing->getId(),
                Table\Generated\RoutesTable::COLUMN_PRIORITY => $priority,
                Table\Generated\RoutesTable::COLUMN_PATH => $route->getPath(),
                Table\Generated\RoutesTable::COLUMN_METADATA => $route->getMetadata() !== null ? json_encode($route->getMetadata()) : null,
            ]);

            $this->routesTable->update($record);

            // assign scopes
            $scopes = $route->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createFromRoute($existing->getCategoryId(), $existing->getId(), $scopes, $context);
            }

            // handle config
            $this->configService->handleConfig($existing->getCategoryId(), $existing->getId(), $existing->getPath(), $config, $context);

            $this->routesTable->commit();
        } catch (\Throwable $e) {
            $this->routesTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($route, $existing, $context));

        return $routeId;
    }

    public function delete(int $routeId, UserContext $context): int
    {
        $existing = $this->routesTable->find($routeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find route');
        }

        if ($existing->getStatus() == Table\Route::STATUS_DELETED) {
            throw new StatusCode\GoneException('Route was deleted');
        }

        // check whether route has a production version
        if ($this->methodTable->hasProductionVersion($existing->getId())) {
            throw new StatusCode\ConflictException('It is not possible to delete a route which contains an active production or deprecated method');
        }

        // delete route
        $record = new Table\Generated\RoutesRow([
            Table\Generated\RoutesTable::COLUMN_ID => $existing->getId(),
            Table\Generated\RoutesTable::COLUMN_STATUS => Table\Route::STATUS_DELETED
        ]);

        $this->routesTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $routeId;
    }

    /**
     * Checks whether the provided path already exists
     */
    public function exists(string $path): int|false
    {
        $condition  = new Condition();
        $condition->equals(Table\Generated\RoutesTable::COLUMN_STATUS, Table\Route::STATUS_ACTIVE);
        $condition->equals(Table\Generated\RoutesTable::COLUMN_PATH, $path);

        $route = $this->routesTable->findOneBy($condition);

        if ($route instanceof Table\Generated\RoutesRow) {
            return $route->getId();
        } else {
            return false;
        }
    }
}
