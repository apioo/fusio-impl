<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Model\Backend\Scope_Create;
use Fusio\Model\Backend\Scope_Route;
use Fusio\Model\Backend\Scope_Update;
use Fusio\Impl\Event\Scope\CreatedEvent;
use Fusio\Impl\Event\Scope\DeletedEvent;
use Fusio\Impl\Event\Scope\UpdatedEvent;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Scope
{
    private Table\Scope $scopeTable;
    private Table\Scope\Route $scopeRouteTable;
    private Table\App\Scope $appScopeTable;
    private Table\User\Scope $userScopeTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Scope $scopeTable, Table\Scope\Route $scopeRouteTable, Table\App\Scope $appScopeTable, Table\User\Scope $userScopeTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->scopeTable      = $scopeTable;
        $this->scopeRouteTable = $scopeRouteTable;
        $this->appScopeTable   = $appScopeTable;
        $this->userScopeTable  = $userScopeTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, Scope_Create $scope, UserContext $context): int
    {
        // check whether scope exists
        if ($this->exists($scope->getName())) {
            throw new StatusCode\BadRequestException('Scope already exists');
        }

        try {
            $this->scopeTable->beginTransaction();

            // create scope
            $record = new Table\Generated\ScopeRow([
                'category_id' => $categoryId,
                'name'        => $scope->getName(),
                'description' => $scope->getDescription() ?? '',
            ]);

            $this->scopeTable->create($record);

            // insert routes
            $scopeId = $this->scopeTable->getLastInsertId();
            $scope->setId($scopeId);

            $this->insertRoutes($scopeId, $scope->getRoutes() ?? []);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($scope, $context));

        return $scopeId;
    }

    public function createFromRoute(int $categoryId, int $routeId, array $scopeNames, UserContext $context): void
    {
        // remove all scopes from this route
        $this->scopeRouteTable->deleteAllFromRoute($routeId);

        // insert new scopes
        foreach ($scopeNames as $scopeName) {
            $scope = $this->scopeTable->findOneByName($scopeName);
            if (!empty($scope)) {
                // assign route to scope
                $this->scopeRouteTable->create(new Table\Generated\ScopeRoutesRow([
                    'scope_id' => $scope['id'],
                    'route_id' => $routeId,
                    'allow'    => 1,
                    'methods'  => 'GET|POST|PUT|PATCH|DELETE',
                ]));
            } else {
                // create new scope
                $route = new Scope_Route();
                $route->setRouteId($routeId);
                $route->setAllow(true);
                $route->setMethods('GET|POST|PUT|PATCH|DELETE');

                $scope = new Scope_Create();
                $scope->setName($scopeName);
                $scope->setRoutes([$route]);

                $this->create($categoryId, $scope, $context);
            }
        }
    }

    public function update(int $scopeId, Scope_Update $scope, UserContext $context): int
    {
        $existing = $this->scopeTable->find($scopeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find scope');
        }

        // check whether this is a system scope
        if (in_array($existing['id'], [1, 2, 3])) {
            throw new StatusCode\BadRequestException('It is not possible to change this scope');
        }

        try {
            $this->scopeTable->beginTransaction();

            $record = new Table\Generated\ScopeRow([
                'id'          => $existing['id'],
                'name'        => $scope->getName(),
                'description' => $scope->getDescription(),
            ]);

            $this->scopeTable->update($record);

            $this->scopeRouteTable->deleteAllFromScope($existing['id']);

            $this->insertRoutes($existing['id'], $scope->getRoutes() ?? []);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($scope, $existing, $context));

        return $scopeId;
    }

    public function delete(int $scopeId, UserContext $context): int
    {
        $existing = $this->scopeTable->find($scopeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find scope');
        }

        // check whether the scope is used by an app or user
        $appScopes = $this->appScopeTable->getCount(new Condition(['scope_id', '=', $existing['id']]));
        if ($appScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assigned to an app. Remove the scope from the app in order to delete the scope');
        }

        $userScopes = $this->userScopeTable->getCount(new Condition(['scope_id', '=', $existing['id']]));
        if ($userScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assigned to an user. Remove the scope from the user in order to delete the scope');
        }

        // check whether this is a system scope
        if (in_array($existing['id'], [1, 2, 3])) {
            throw new StatusCode\BadRequestException('It is not possible to change this scope');
        }

        try {
            $this->scopeTable->beginTransaction();

            // delete all routes assigned to the scope
            $this->scopeRouteTable->deleteAllFromScope($existing['id']);

            $record = new Table\Generated\ScopeRow([
                'id' => $existing['id']
            ]);

            $this->scopeTable->delete($record);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $scopeId;
    }

    /**
     * Returns all scope names which are valid for the app and the user. The scopes are a comma separated list
     */
    public function getValidScopes(string $scopes, ?int $appId, ?int $userId): array
    {
        $scopes = self::split($scopes);

        if ($appId !== null) {
            $scopes = Table\Scope::getNames($this->appScopeTable->getValidScopes($appId, $scopes));
        }
        if ($userId !== null) {
            $scopes = Table\Scope::getNames($this->userScopeTable->getValidScopes($userId, $scopes));
        }

        return $scopes;
    }

    public function exists(string $name): int|false
    {
        $condition  = new Condition();
        $condition->equals('name', $name);

        $scope = $this->scopeTable->findOneBy($condition);

        if (!empty($scope)) {
            return $scope['id'];
        } else {
            return false;
        }
    }

    /**
     * @param Scope_Route[] $routes
     */
    protected function insertRoutes(int $scopeId, ?array $routes): void
    {
        if (!empty($routes) && is_array($routes)) {
            foreach ($routes as $route) {
                if ($route->getAllow()) {
                    $this->scopeRouteTable->create(new Table\Generated\ScopeRoutesRow([
                        'scope_id' => $scopeId,
                        'route_id' => $route->getRouteId(),
                        'allow'    => $route->getAllow() ? 1 : 0,
                        'methods'  => $route->getMethods(),
                    ]));
                }
            }
        }
    }

    public static function split(string $scopes): array
    {
        if (str_contains($scopes, ',')) {
            return explode(',', $scopes);
        } else {
            return explode(' ', $scopes);
        }
    }
}
