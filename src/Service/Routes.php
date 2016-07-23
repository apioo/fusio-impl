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

use Fusio\Impl\Service\Routes\Deploy;
use Fusio\Impl\Service\Routes\Relation;
use Fusio\Impl\Table\Routes as TableRoutes;
use Fusio\Impl\Table\Routes\Method as TableRoutesMethod;
use Fusio\Impl\Table\Scope\Route as TableScopeRoute;
use PSX\Api\Resource;
use PSX\DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

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
     * @var \Fusio\Impl\Service\Routes\Deploy
     */
    protected $deploy;

    /**
     * @var \Fusio\Impl\Service\Routes\Relation
     */
    protected $relation;

    public function __construct(TableRoutes $routesTable, TableRoutesMethod $routesMethodTable, TableScopeRoute $scopeRoutesTable, Deploy $deploy, Relation $relation)
    {
        $this->routesTable       = $routesTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->scopeRoutesTable  = $scopeRoutesTable;
        $this->deploy            = $deploy;
        $this->relation          = $relation;
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

    public function create($path, $config)
    {
        // check whether route exists
        $condition  = new Condition();
        $condition->equals('status', TableRoutes::STATUS_ACTIVE);
        $condition->equals('path', $path);

        $route = $this->routesTable->getOneBy($condition);

        if (!empty($route)) {
            throw new StatusCode\BadRequestException('Route already exists');
        }

        try {
            $this->routesTable->beginTransaction();

            // create route
            $this->routesTable->create([
                'status'     => TableRoutes::STATUS_ACTIVE,
                'methods'    => 'GET|POST|PUT|DELETE',
                'path'       => $path,
                'controller' => 'Fusio\Impl\Controller\SchemaApiController',
            ]);

            // get last insert id
            $routeId = $this->routesTable->getLastInsertId();

            $this->handleConfig($routeId, $config);

            $this->routesTable->commit();
        } catch (\Exception $e) {
            $this->routesTable->rollBack();

            throw $e;
        }
    }

    public function update($routeId, $config)
    {
        $route = $this->routesTable->get($routeId);

        if (!empty($route)) {
            if ($route['status'] == TableRoutes::STATUS_DELETED) {
                throw new StatusCode\GoneException('Route was deleted');
            }

            try {
                $this->routesTable->beginTransaction();

                $this->handleConfig($route->id, $config);

                $this->routesTable->commit();
            } catch (\Exception $e) {
                $this->routesTable->rollBack();

                throw $e;
            }
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

    protected function handleConfig($routeId, $result)
    {
        // get existing methods
        $existingMethods = $this->routesMethodTable->getMethods($routeId);

        // insert methods
        $availableMethods = ['GET', 'POST', 'PUT', 'DELETE'];

        foreach ($result as $version) {
            // check version
            $ver = isset($version['version']) ? intval($version['version']) : 0;
            if ($ver <= 0) {
                throw new StatusCode\BadRequestException('Version must be a positive integer');
            }

            // check status
            $status = isset($version['status']) ? $version['status'] : 0;
            if (!in_array($status, [Resource::STATUS_DEVELOPMENT, Resource::STATUS_ACTIVE, Resource::STATUS_DEPRECATED, Resource::STATUS_CLOSED])) {
                throw new StatusCode\BadRequestException('Invalid status value');
            }

            // delete all existing development versions
            $this->routesMethodTable->deleteAllFromRoute($routeId, $ver, Resource::STATUS_DEVELOPMENT);

            // parse methods
            $methods = isset($version['methods']) ? $version['methods'] : [];

            foreach ($methods as $method => $config) {
                // check method
                if (!in_array($method, $availableMethods)) {
                    throw new StatusCode\BadRequestException('Invalid request method');
                }

                $active = isset($config['active']) ? $config['active'] : false;
                $public = isset($config['public']) ? $config['public'] : false;

                // find existing method
                $existingMethod = null;
                foreach ($existingMethods as $index => $row) {
                    if ($row['version'] == $ver && $row['method'] == $method) {
                        $existingMethod = $row;
                    }
                }

                if ($status == Resource::STATUS_DEVELOPMENT) {
                    $data = [
                        'routeId'  => $routeId,
                        'method'   => $method,
                        'version'  => $ver,
                        'status'   => $status,
                        'active'   => $active ? 1 : 0,
                        'public'   => $public ? 1 : 0,
                        'request'  => isset($config['request'])  ? $config['request']  : null,
                        'response' => isset($config['response']) ? $config['response'] : null,
                        'action'   => isset($config['action'])   ? $config['action']   : null,
                    ];

                    $this->routesMethodTable->create($data);
                } elseif ($active === true) {
                    // if the method is not in development mode we create only
                    // the schema/action cache on the transition from dev to
                    // prod in every other case we dont change any values
                    if ($existingMethod === null) {
                        throw new StatusCode\BadRequestException('A new resource can only start in development mode');
                    }

                    if ($existingMethod['status'] == Resource::STATUS_DEVELOPMENT && $status == Resource::STATUS_ACTIVE) {
                        // deploy method to active
                        $this->deploy->deploy($existingMethod);
                    } elseif ($existingMethod['status'] != $status) {
                        // change only the status if not in development
                        $this->routesMethodTable->update([
                            'id'     => $existingMethod['id'],
                            'status' => $status,
                        ]);
                    }
                }
            }
        }

        // update relations
        $this->relation->updateRelations($routeId);
    }
}
