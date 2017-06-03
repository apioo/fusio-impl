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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
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
     * @var \Fusio\Impl\Service\Routes\Config
     */
    protected $configService;

    public function __construct(Table\Routes $routesTable, Table\Routes\Method $methodTable, Service\Routes\Config $configService)
    {
        $this->routesTable   = $routesTable;
        $this->methodTable   = $methodTable;
        $this->configService = $configService;
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

            $this->configService->handleConfig($routeId, $path, $config);

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

                $this->configService->handleConfig($route->id, $route->path, $config);

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
            if ($this->methodTable->hasProductionVersion($route->id)) {
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
}
