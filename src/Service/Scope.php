<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Event\ScopeEvents;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Scope
{
    /**
     * @var \Fusio\Impl\Table\Scope
     */
    protected $scopeTable;

    /**
     * @var \Fusio\Impl\Table\Scope\Route
     */
    protected $scopeRouteTable;

    /**
     * @var \Fusio\Impl\Table\App\Scope
     */
    protected $appScopeTable;

    /**
     * @var \Fusio\Impl\Table\User\Scope
     */
    protected $userScopeTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Scope $scopeTable
     * @param \Fusio\Impl\Table\Scope\Route $scopeRouteTable
     * @param \Fusio\Impl\Table\App\Scope $appScopeTable
     * @param \Fusio\Impl\Table\User\Scope $userScopeTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Scope $scopeTable, Table\Scope\Route $scopeRouteTable, Table\App\Scope $appScopeTable, Table\User\Scope $userScopeTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->scopeTable      = $scopeTable;
        $this->scopeRouteTable = $scopeRouteTable;
        $this->appScopeTable   = $appScopeTable;
        $this->userScopeTable  = $userScopeTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create($name, $description, array $routes = null, UserContext $context)
    {
        // check whether scope exists
        $condition  = new Condition();
        $condition->equals('name', $name);

        $scope = $this->scopeTable->getOneBy($condition);

        if (!empty($scope)) {
            throw new StatusCode\BadRequestException('Scope already exists');
        }

        try {
            $this->scopeTable->beginTransaction();

            // create scope
            $record = [
                'name'        => $name,
                'description' => $description ?: '',
            ];

            $this->scopeTable->create($record);

            // insert routes
            $scopeId = $this->scopeTable->getLastInsertId();

            $this->insertRoutes($scopeId, $routes);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(ScopeEvents::CREATE, new CreatedEvent($scopeId, $record, $routes, $context));
    }

    public function createFromRoute($routeId, array $scopeNames, UserContext $context)
    {
        // remove all scopes from this route
        $this->scopeRouteTable->deleteAllFromRoute($routeId);

        // insert new scopes
        foreach ($scopeNames as $scopeName) {
            $scope = $this->scopeTable->getOneBy(new Condition(['name', '=', $scopeName]));

            if (!empty($scope)) {
                // assign route to scope
                $this->scopeRouteTable->create([
                    'scopeId' => $scope['id'],
                    'routeId' => $routeId,
                    'allow'   => 1,
                    'methods' => 'GET|POST|PUT|PATCH|DELETE',
                ]);
            } else {
                // create new scope
                $route = (object) [
                    'routeId' => $routeId,
                    'allow'   => 1,
                    'methods' => 'GET|POST|PUT|PATCH|DELETE',
                ];

                $this->create($scopeName, null, [$route], $context);
            }
        }
    }

    public function update($scopeId, $name, $description, array $routes = null, UserContext $context)
    {
        $scope = $this->scopeTable->get($scopeId);

        if (empty($scope)) {
            throw new StatusCode\NotFoundException('Could not find scope');
        }

        // check whether this is a system scope
        if (in_array($scope['id'], [1, 2, 3])) {
            throw new StatusCode\BadRequestException('It is not possible to change this scope');
        }

        try {
            $this->scopeTable->beginTransaction();

            $record = [
                'id'          => $scope['id'],
                'name'        => $name,
                'description' => $description,
            ];

            $this->scopeTable->update($record);

            $this->scopeRouteTable->deleteAllFromScope($scope['id']);

            $this->insertRoutes($scope['id'], $routes);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(ScopeEvents::UPDATE, new UpdatedEvent($scopeId, $record, $routes, $scope, $context));
    }

    public function delete($scopeId, UserContext $context)
    {
        $scope = $this->scopeTable->get($scopeId);

        if (empty($scope)) {
            throw new StatusCode\NotFoundException('Could not find scope');
        }

        // check whether the scope is used by an app or user
        $appScopes = $this->appScopeTable->getCount(new Condition(['scopeId', '=', $scope['id']]));
        if ($appScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assigned to an app. Remove the scope from the app in order to delete the scope');
        }

        $userScopes = $this->userScopeTable->getCount(new Condition(['scopeId', '=', $scope['id']]));
        if ($userScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assgined to an user. Remove the scope from the user in order to delete the scope');
        }

        // check whether this is a system scope
        if (in_array($scope['id'], [1, 2, 3])) {
            throw new StatusCode\BadRequestException('It is not possible to change this scope');
        }

        try {
            $this->scopeTable->beginTransaction();

            // delete all routes assigned to the scope
            $this->scopeRouteTable->deleteAllFromScope($scope['id']);

            $record = [
                'id' => $scope['id']
            ];

            $this->scopeTable->delete($record);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(ScopeEvents::DELETE, new DeletedEvent($scopeId, $scope, $context));
    }

    /**
     * Returns all scope names which are valid for the app and the user. The
     * scopes are a comma separated list. All scopes which are listed in the
     * $exclude array are excluded
     *
     * @param integer $appId
     * @param integer $userId
     * @param string $scopes
     * @param array $exclude
     * @return array
     */
    public function getValidScopes($appId, $userId, $scopes, array $exclude = array())
    {
        $scopes = self::split($scopes);
        $scopes = Table\Scope::getNames($this->appScopeTable->getValidScopes($appId, $scopes, $exclude));
        $scopes = Table\Scope::getNames($this->userScopeTable->getValidScopes($userId, $scopes, $exclude));

        return $scopes;
    }

    protected function insertRoutes($scopeId, $routes)
    {
        if (!empty($routes) && is_array($routes)) {
            foreach ($routes as $route) {
                if ($route->allow) {
                    $this->scopeRouteTable->create(array(
                        'scopeId' => $scopeId,
                        'routeId' => $route->routeId,
                        'allow'   => $route->allow ? 1 : 0,
                        'methods' => $route->methods,
                    ));
                }
            }
        }
    }

    public static function split($scopes)
    {
        if (strpos($scopes, ',') !== false) {
            return explode(',', $scopes);
        } else {
            return explode(' ', $scopes);
        }
    }
}
