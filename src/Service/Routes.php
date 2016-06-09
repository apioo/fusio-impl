<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use Fusio\Impl\Table\Routes as TableRoutes;
use Fusio\Impl\Table\Routes\Method as TableRoutesMethod;
use Fusio\Impl\Table\Scope\Route as TableScopeRoute;
use Fusio\Impl\Service\Routes\DependencyManager;
use PSX\Api\Resource;
use PSX\DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Sql\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Fields;

/**
 * Routes
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Routes
{
    /**
     * @var \Fusio\Impl\Table\Routes
     */
    protected $routesTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $methodTable;

    /**
     * @var \Fusio\Impl\Table\Scope\Route
     */
    protected $scopeRoutesTable;

    /**
     * @var \Fusio\Impl\Service\Routes\DependencyManager
     */
    protected $dependencyManager;

    public function __construct(TableRoutes $routesTable, TableRoutesMethod $routesMethodTable, TableScopeRoute $scopeRoutesTable, DependencyManager $dependencyManager)
    {
        $this->routesTable       = $routesTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->scopeRoutesTable  = $scopeRoutesTable;
        $this->dependencyManager = $dependencyManager;
    }

    public function getAll($startIndex = 0, $search = null)
    {
        return $this->routesTable->getRoutes($startIndex, $search);
    }

    public function get($routeId)
    {
        $route = $this->routesTable->getRoute($routeId);

        if (!empty($route)) {
            if ($route['status'] == TableRoutes::STATUS_DELETED) {
                throw new StatusCode\GoneException('Route was deleted');
            }

            return $route;
        } else {
            throw new StatusCode\NotFoundException('Could not find route');
        }
    }

    public function create($path, $methods)
    {
        // check whether route exists
        $condition  = new Condition();
        $condition->equals('status', TableRoutes::STATUS_ACTIVE);
        $condition->equals('path', $path);

        $route = $this->routesTable->getOneBy($condition);

        if (!empty($route)) {
            throw new StatusCode\BadRequestException('Route already exists');
        }

        // create route
        $this->routesTable->create([
            'status'     => TableRoutes::STATUS_ACTIVE,
            'methods'    => 'GET|POST|PUT|DELETE',
            'path'       => $path,
            'controller' => 'Fusio\Impl\Controller\SchemaApiController',
        ]);

        // get last insert id
        $routeId = $this->routesTable->getLastInsertId();

        $this->handleMethods($routeId, $methods);
    }

    public function update($routeId, $methods)
    {
        $route = $this->routesTable->get($routeId);

        if (!empty($route)) {
            if ($route['status'] == TableRoutes::STATUS_DELETED) {
                throw new StatusCode\GoneException('Route was deleted');
            }

            $this->handleMethods($route->id, $methods);
        } else {
            throw new StatusCode\NotFoundException('Could not find route');
        }
    }

    public function delete($routeId)
    {
        $route = $this->routesTable->get($routeId);

        if (!empty($route)) {
            if ($route['status'] == TableRoutes::STATUS_DELETED) {
                throw new StatusCode\GoneException('Route was deleted');
            }

            // check whether route has a production version
            if ($this->routesMethodTable->hasProductionVersion($route->id)) {
                throw new StatusCode\ConflictException('It is not possible to delete a route which contains a active production or deprecated method');
            }

            // delete route
            $this->routesTable->update(array(
                'id'     => $route->id,
                'status' => TableRoutes::STATUS_DELETED
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find route');
        }
    }

    protected function handleMethods($routeId, $methods)
    {
        // delete existing
        $this->routesMethodTable->deleteAllFromRoute($routeId);

        // insert methods
        $availableMethods = ['GET', 'POST', 'PUT', 'DELETE'];

        foreach ($methods as $config) {
            // check method
            $method = isset($config['method']) ? $config['method'] : null;
            if (!in_array($method, $availableMethods)) {
                throw new StatusCode\BadRequestException('Invalid request method');
            }

            // check version
            $version = isset($config['version']) ? intval($config['version']) : 0;
            if ($version <= 0) {
                throw new StatusCode\BadRequestException('Version must be a positive integer');
            }

            // check status
            $status = isset($config['status']) ? $config['status'] : 0;
            if (!in_array($status, [Resource::STATUS_DEVELOPMENT, Resource::STATUS_ACTIVE, Resource::STATUS_DEPRECATED, Resource::STATUS_CLOSED])) {
                throw new StatusCode\BadRequestException('Invalid status value');
            }

            $active = isset($config['active']) ? $config['active'] : false;
            $public = isset($config['public']) ? $config['public'] : false;

            $this->routesMethodTable->create([
                'routeId'  => $routeId,
                'method'   => $method,
                'version'  => $version,
                'status'   => $status,
                'active'   => $active ? 1 : 0,
                'public'   => $public ? 1 : 0,
                'request'  => isset($config['request'])  ? $config['request']  : null,
                'response' => isset($config['response']) ? $config['response'] : null,
                'action'   => isset($config['action'])   ? $config['action']   : null,
            ]);
        }

        // update dependency links
        $this->dependencyManager->updateDependencyLinks($routeId);
    }
}
