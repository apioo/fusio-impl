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
use Fusio\Impl\Event\Scope\CreatedEvent;
use Fusio\Impl\Event\Scope\DeletedEvent;
use Fusio\Impl\Event\Scope\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ScopeCreate;
use Fusio\Model\Backend\ScopeRoute;
use Fusio\Model\Backend\ScopeUpdate;
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

    public function create(int $categoryId, ScopeCreate $scope, UserContext $context): int
    {
        $name = $scope->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        // check whether scope exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Scope already exists');
        }

        // create scope
        try {
            $this->scopeTable->beginTransaction();

            $record = new Table\Generated\ScopeRow([
                Table\Generated\ScopeTable::COLUMN_CATEGORY_ID => $categoryId,
                Table\Generated\ScopeTable::COLUMN_NAME => $scope->getName(),
                Table\Generated\ScopeTable::COLUMN_DESCRIPTION => $scope->getDescription() ?? '',
                Table\Generated\ScopeTable::COLUMN_METADATA => $scope->getMetadata() !== null ? json_encode($scope->getMetadata()) : null,
            ]);

            $this->scopeTable->create($record);

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
            if ($scope instanceof Table\Generated\ScopeRow) {
                // assign route to scope
                $this->scopeRouteTable->create(new Table\Generated\ScopeRoutesRow([
                    Table\Generated\ScopeRoutesTable::COLUMN_SCOPE_ID => $scope->getId(),
                    Table\Generated\ScopeRoutesTable::COLUMN_ROUTE_ID => $routeId,
                    Table\Generated\ScopeRoutesTable::COLUMN_ALLOW => 1,
                    Table\Generated\ScopeRoutesTable::COLUMN_METHODS => 'GET|POST|PUT|PATCH|DELETE',
                ]));
            } else {
                // create new scope
                $route = new ScopeRoute();
                $route->setRouteId($routeId);
                $route->setAllow(true);
                $route->setMethods('GET|POST|PUT|PATCH|DELETE');

                $scope = new ScopeCreate();
                $scope->setName($scopeName);
                $scope->setRoutes([$route]);

                $this->create($categoryId, $scope, $context);
            }
        }
    }

    public function update(int $scopeId, ScopeUpdate $scope, UserContext $context): int
    {
        $existing = $this->scopeTable->find($scopeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find scope');
        }

        // check whether this is a system scope
        if (in_array($existing->getId(), [1, 2, 3])) {
            throw new StatusCode\BadRequestException('It is not possible to change this scope');
        }

        try {
            $this->scopeTable->beginTransaction();

            $record = new Table\Generated\ScopeRow([
                Table\Generated\ScopeTable::COLUMN_ID => $existing->getId(),
                Table\Generated\ScopeTable::COLUMN_NAME => $scope->getName(),
                Table\Generated\ScopeTable::COLUMN_DESCRIPTION => $scope->getDescription(),
                Table\Generated\ScopeTable::COLUMN_METADATA => $scope->getMetadata() !== null ? json_encode($scope->getMetadata()) : null,
            ]);

            $this->scopeTable->update($record);

            $this->scopeRouteTable->deleteAllFromScope($existing->getId());

            $this->insertRoutes($existing->getId(), $scope->getRoutes() ?? []);

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
        $appScopes = $this->appScopeTable->getCount(new Condition([Table\Generated\AppScopeTable::COLUMN_SCOPE_ID, '=', $existing->getId()]));
        if ($appScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assigned to an app. Remove the scope from the app in order to delete the scope');
        }

        $userScopes = $this->userScopeTable->getCount(new Condition([Table\Generated\UserScopeTable::COLUMN_SCOPE_ID, '=', $existing->getId()]));
        if ($userScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assigned to an user. Remove the scope from the user in order to delete the scope');
        }

        // check whether this is a system scope
        if (in_array($existing->getId(), [1, 2, 3])) {
            throw new StatusCode\BadRequestException('It is not possible to change this scope');
        }

        try {
            $this->scopeTable->beginTransaction();

            // delete all routes assigned to the scope
            $this->scopeRouteTable->deleteAllFromScope($existing->getId());

            $record = new Table\Generated\ScopeRow([
                Table\Generated\ScopeTable::COLUMN_ID => $existing->getId()
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
        $condition->equals(Table\Generated\ScopeTable::COLUMN_NAME, $name);

        $scope = $this->scopeTable->findOneBy($condition);

        if ($scope instanceof Table\Generated\ScopeRow) {
            return $scope->getId();
        } else {
            return false;
        }
    }

    /**
     * @param ScopeRoute[] $routes
     */
    protected function insertRoutes(int $scopeId, ?array $routes): void
    {
        if (!empty($routes)) {
            foreach ($routes as $route) {
                if ($route->getAllow()) {
                    $this->scopeRouteTable->create(new Table\Generated\ScopeRoutesRow([
                        Table\Generated\ScopeRoutesTable::COLUMN_SCOPE_ID => $scopeId,
                        Table\Generated\ScopeRoutesTable::COLUMN_ROUTE_ID => $route->getRouteId(),
                        Table\Generated\ScopeRoutesTable::COLUMN_ALLOW => $route->getAllow() ? 1 : 0,
                        Table\Generated\ScopeRoutesTable::COLUMN_METHODS => $route->getMethods(),
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
