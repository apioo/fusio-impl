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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Route_Create;
use Fusio\Impl\Backend\Model\Route_Update;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Event\Route\CreatedEvent;
use Fusio\Impl\Event\Route\DeletedEvent;
use Fusio\Impl\Event\Route\UpdatedEvent;
use Fusio\Impl\Event\RoutesEvents;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Route
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Route
{
    /**
     * @var \Fusio\Impl\Table\Route
     */
    private $routesTable;

    /**
     * @var \Fusio\Impl\Table\Route\Method
     */
    private $methodTable;

    /**
     * @var \Fusio\Impl\Service\Scope
     */
    private $scopeService;

    /**
     * @var \Fusio\Impl\Service\Route\Config
     */
    private $configService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Route $routesTable
     * @param \Fusio\Impl\Table\Route\Method $methodTable
     * @param \Fusio\Impl\Service\Scope $scopeService
     * @param \Fusio\Impl\Service\Route\Config $configService
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Route $routesTable, Table\Route\Method $methodTable, Service\Scope $scopeService, Route\Config $configService, EventDispatcherInterface $eventDispatcher)
    {
        $this->routesTable     = $routesTable;
        $this->methodTable     = $methodTable;
        $this->scopeService    = $scopeService;
        $this->configService   = $configService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Route_Create $route, UserContext $context)
    {
        Route\Validator::assertPath($route->getPath());

        // check whether route exists
        if ($this->exists($route->getPath())) {
            throw new StatusCode\BadRequestException('Route already exists');
        }

        if ($route->getPriority() === null) {
            $route->setPriority($this->routesTable->getMaxPriority() + 1);
        } elseif ($route->getPriority()>= 0x1000000) {
            throw new StatusCode\GoneException('Priority can not be greater or equal to ' . 0x1000000);
        }

        if (!empty($route->getController())) {
            if (!class_exists($route->getController())) {
                throw new StatusCode\BadRequestException('Provided controller does not exist');
            }
        } else {
            $route->setController(SchemaApiController::class);
        }

        try {
            $this->routesTable->beginTransaction();

            // create route
            $record = [
                'status'     => Table\Route::STATUS_ACTIVE,
                'priority'   => $route->getPriority(),
                'methods'    => 'ANY',
                'path'       => $route->getPath(),
                'controller' => $route->getController(),
            ];

            $this->routesTable->create($record);

            // get last insert id
            $routeId = $this->routesTable->getLastInsertId();
            $route->setId($routeId);

            // assign scopes
            $scopes = $route->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createFromRoute($routeId, $scopes, $context);
            }

            // handle config
            $this->configService->handleConfig($route->getId(), $route->getPath(), $route->getConfig(), $context);

            $this->routesTable->commit();
        } catch (\Throwable $e) {
            $this->routesTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($route, $context));

        return $routeId;
    }

    public function update(int $routeId, Route_Update $route, UserContext $context)
    {
        $existing = $this->routesTable->get($routeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find route');
        }

        if ($existing['status'] == Table\Route::STATUS_DELETED) {
            throw new StatusCode\GoneException('Route was deleted');
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
            $record = [
                'id'       => $existing['id'],
                'priority' => $priority,
            ];

            $this->routesTable->update($record);

            // assign scopes
            $scopes = $route->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createFromRoute($existing['id'], $scopes, $context);
            }

            // handle config
            $this->configService->handleConfig($existing['id'], $existing['path'], $route->getConfig(), $context);

            $this->routesTable->commit();
        } catch (\Throwable $e) {
            $this->routesTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($route, $existing, $context));
    }

    public function delete(int $routeId, UserContext $context)
    {
        $existing = $this->routesTable->get($routeId);

        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find route');
        }

        if ($existing['status'] == Table\Route::STATUS_DELETED) {
            throw new StatusCode\GoneException('Route was deleted');
        }

        // check whether route has a production version
        if ($this->methodTable->hasProductionVersion($existing['id'])) {
            throw new StatusCode\ConflictException('It is not possible to delete a route which contains an active production or deprecated method');
        }

        // delete route
        $record = [
            'id'     => $existing['id'],
            'status' => Table\Route::STATUS_DELETED
        ];

        $this->routesTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }

    /**
     * Checks whether the provided path already exists
     * 
     * @param string $path
     * @return bool|mixed
     */
    public function exists(string $path)
    {
        $condition  = new Condition();
        $condition->equals('status', Table\Route::STATUS_ACTIVE);
        $condition->equals('path', $path);

        $route = $this->routesTable->getOneBy($condition);

        if (!empty($route)) {
            return $route['id'];
        } else {
            return false;
        }
    }
}
