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

use Fusio\Impl\Service\Routes\Deploy;
use Fusio\Impl\Service\Routes\Relation;
use Fusio\Impl\Table;
use PSX\Api\ListingInterface;
use PSX\Api\Resource;
use PSX\Framework\Api\CachedListing;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Routes
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
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

    /**
     * @var \PSX\Api\ListingInterface
     */
    protected $listing;

    public function __construct(Table\Routes $routesTable, Table\Routes\Method $routesMethodTable, Table\Scope\Route $scopeRoutesTable, Deploy $deploy, Relation $relation, ListingInterface $listing)
    {
        $this->routesTable       = $routesTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->scopeRoutesTable  = $scopeRoutesTable;
        $this->deploy            = $deploy;
        $this->relation          = $relation;
        $this->listing           = $listing;
    }

    public function create($path, $config)
    {
        // check whether route exists
        $condition  = new Condition();
        $condition->equals('status', Table\Routes::STATUS_ACTIVE);
        $condition->equals('path', $path);

        $route = $this->routesTable->getOneBy($condition);

        if (!empty($route)) {
            throw new StatusCode\BadRequestException('Route already exists');
        }

        try {
            $this->routesTable->beginTransaction();

            // create route
            $this->routesTable->create([
                'status'     => Table\Routes::STATUS_ACTIVE,
                'methods'    => 'GET|POST|PUT|DELETE',
                'path'       => $path,
                'controller' => 'Fusio\Impl\Controller\SchemaApiController',
            ]);

            // get last insert id
            $routeId = $this->routesTable->getLastInsertId();

            $this->handleConfig($routeId, $path, $config);

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
            if ($route['status'] == Table\Routes::STATUS_DELETED) {
                throw new StatusCode\GoneException('Route was deleted');
            }

            try {
                $this->routesTable->beginTransaction();

                $this->handleConfig($route->id, $route->path, $config);

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
            if ($route['status'] == Table\Routes::STATUS_DELETED) {
                throw new StatusCode\GoneException('Route was deleted');
            }

            // check whether route has a production version
            if ($this->routesMethodTable->hasProductionVersion($route->id)) {
                throw new StatusCode\ConflictException('It is not possible to delete a route which contains a active production or deprecated method');
            }

            // delete route
            $this->routesTable->update(array(
                'id'     => $route->id,
                'status' => Table\Routes::STATUS_DELETED
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find route');
        }
    }

    /**
     * Method which handles data change of each API method. Basically an API
     * method can only change if it is in development mode. In every other
     * case we can only change the status
     *
     * @param integer $routeId
     * @param string $path
     * @param \PSX\Record\RecordInterface $result
     */
    protected function handleConfig($routeId, $path, $result)
    {
        // get existing methods
        $existingMethods = $this->routesMethodTable->getMethods($routeId, null, false, null);

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

            // invalidate resource cache
            if ($this->listing instanceof CachedListing) {
                $this->listing->invalidateResource($path, $ver);
            }

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
                    // we can change the API only if we are in development mode
                    if ($existingMethod === null || $existingMethod['status'] == Resource::STATUS_DEVELOPMENT) {
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
                    } else {
                        $this->routesMethodTable->update([
                            'id'       => $existingMethod['id'],
                            'routeId'  => $routeId,
                            'method'   => $method,
                            'version'  => $ver,
                            'status'   => $status,
                            'active'   => $active ? 1 : 0,
                            'public'   => $public ? 1 : 0,
                            'request'  => isset($config['request'])  ? $config['request']  : null,
                            'response' => isset($config['response']) ? $config['response'] : null,
                            'action'   => isset($config['action'])   ? $config['action']   : null,
                        ]);
                    }
                } elseif ($active === true) {
                    // if the method is not in development mode we create only
                    // the schema/action cache on the transition from dev to
                    // prod in every other case we dont change any values except
                    // for the status
                    if ($existingMethod === null) {
                        throw new StatusCode\BadRequestException('A new resource can only start in development mode');
                    }

                    if ($existingMethod['status'] == Resource::STATUS_DEVELOPMENT && $status == Resource::STATUS_ACTIVE) {
                        // deploy method to active
                        $this->deploy->deploy($existingMethod);
                    } elseif ($existingMethod['status'] != $status) {
                        // we can not transition directly from development to deprecated or closed
                        if ($existingMethod['status'] == Resource::STATUS_DEVELOPMENT && in_array($status, [Resource::STATUS_DEPRECATED, Resource::STATUS_CLOSED])) {
                            throw new StatusCode\BadRequestException('A route can only transition from development to production');
                        }

                        // change only the status if not in development
                        $this->routesMethodTable->update([
                            'id'     => $existingMethod['id'],
                            'status' => $status,
                        ]);
                    }
                }
            }
        }

        // invalidate resource cache
        if ($this->listing instanceof CachedListing) {
            $this->listing->invalidateResource($path);
        }

        // update relations
        $this->relation->updateRelations($routeId);
    }
}
