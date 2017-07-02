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

namespace Fusio\Impl\Service\Routes;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Routes\DeployedEvent;
use Fusio\Impl\Event\RoutesEvents;
use Fusio\Impl\Table;
use PSX\Api\ListingInterface;
use PSX\Api\Resource;
use PSX\Framework\Api\CachedListing;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Config
{
    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $methodTable;

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

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Routes\Method $methodTable
     * @param \Fusio\Impl\Service\Routes\Deploy $deploy
     * @param \Fusio\Impl\Service\Routes\Relation $relation
     * @param \PSX\Api\ListingInterface $listing
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Routes\Method $methodTable, Deploy $deploy, Relation $relation, ListingInterface $listing, EventDispatcherInterface $eventDispatcher)
    {
        $this->methodTable     = $methodTable;
        $this->deploy          = $deploy;
        $this->relation        = $relation;
        $this->listing         = $listing;
        $this->eventDispatcher = $eventDispatcher;
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
    public function handleConfig($routeId, $path, $result, UserContext $context)
    {
        // get existing methods
        $existingMethods = $this->methodTable->getMethods($routeId, null, false, null);

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
            $this->methodTable->deleteAllFromRoute($routeId, $ver, Resource::STATUS_DEVELOPMENT);

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
                    // Change the API only if we are in development mode. We
                    // create an entry also in development mode because we have
                    // previously deleted the entry
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

                        $this->methodTable->create($data);
                    } else {
                        $this->methodTable->update([
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

                        // dispatch event
                        $this->eventDispatcher->dispatch(RoutesEvents::DEPLOY, new DeployedEvent($routeId, $existingMethod, $context));
                    } elseif ($existingMethod['status'] != $status) {
                        // we can not transition directly from development to
                        // deprecated or closed
                        if ($existingMethod['status'] == Resource::STATUS_DEVELOPMENT && in_array($status, [Resource::STATUS_DEPRECATED, Resource::STATUS_CLOSED])) {
                            throw new StatusCode\BadRequestException('A route can only transition from development to production');
                        }

                        // change only the status if not in development
                        $this->methodTable->update([
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
