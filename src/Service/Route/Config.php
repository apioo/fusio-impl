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

namespace Fusio\Impl\Service\Route;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Framework\Filter\Filter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\RouteMethod;
use Fusio\Model\Backend\RouteVersion;
use PSX\Api\Listing\CachedListing;
use PSX\Api\ListingInterface;
use PSX\Api\Resource;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Config
{
    private Table\Route\Method $methodTable;
    private Table\Route\Response $responseTable;
    private ListingInterface $listing;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Route\Method $methodTable, Table\Route\Response $responseTable, ListingInterface $listing, EventDispatcherInterface $eventDispatcher)
    {
        $this->methodTable     = $methodTable;
        $this->responseTable   = $responseTable;
        $this->listing         = $listing;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Method which handles data change of each API method. Basically an API method can only change if it is in
     * development mode. In every other case we can only change the status
     *
     * @param RouteVersion[] $versions
     */
    public function handleConfig(int $categoryId, int $routeId, string $path, array $versions, UserContext $context)
    {
        $availableMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        foreach ($versions as $version) {
            // check version
            $ver = $version->getVersion() ?? 0;
            if ($ver <= 0) {
                throw new StatusCode\BadRequestException('Version must be a positive integer');
            }

            // check status
            $status = $version->getStatus() ?? 0;
            if (!in_array($status, [Resource::STATUS_DEVELOPMENT, Resource::STATUS_ACTIVE, Resource::STATUS_DEPRECATED, Resource::STATUS_CLOSED])) {
                throw new StatusCode\BadRequestException('Invalid status value');
            }

            $existingMethods = $this->methodTable->getMethods($routeId, $ver, null);

            if ($status == Resource::STATUS_DEVELOPMENT) {
                // invalidate resource cache
                if ($this->listing instanceof CachedListing) {
                    $this->listing->invalidateResource($path, (string) $ver);
                }
                
                // delete all responses from existing responses
                foreach ($existingMethods as $existingMethod) {
                    $this->responseTable->deleteAllFromMethod($existingMethod['id']);
                }

                // delete all methods from existing versions
                $this->methodTable->deleteAllFromRoute($routeId, $ver);

                // parse methods
                $methods = $version->getMethods() ?? [];
                foreach ($methods as $method => $config) {
                    // check method
                    if (!in_array($method, $availableMethods)) {
                        throw new StatusCode\BadRequestException('Invalid request method');
                    }

                    // create method
                    $methodId = $this->createMethod($routeId, $method, $ver, $status, $config, $path);

                    // create responses
                    $this->createResponses($methodId, $config);
                }
            } else {
                // update only existing methods
                foreach ($existingMethods as $existingMethod) {
                    $record = new Table\Generated\RoutesMethodRow([
                        Table\Generated\RoutesMethodTable::COLUMN_ID => $existingMethod['id'],
                        Table\Generated\RoutesMethodTable::COLUMN_STATUS => $status,
                    ]);

                    $this->methodTable->update($record);
                }
            }
        }

        // invalidate resource cache
        if ($this->listing instanceof CachedListing) {
            $this->listing->invalidateResourceIndex(new Filter($categoryId));
            $this->listing->invalidateResourceCollection(null, new Filter($categoryId));
            $this->listing->invalidateResource($path);
        }
    }

    private function createMethod(int $routeId, string $method, int $ver, int $status, RouteMethod $config, string $path): int
    {
        $active      = $config->getActive() ?? false;
        $public      = $config->getPublic() ?? false;
        $description = $config->getDescription();
        $operationId = $config->getOperationId();
        $parameters  = $config->getParameters();
        $request     = $config->getRequest();
        $action      = $config->getAction();
        $costs       = $config->getCosts();

        if (empty($operationId)) {
            $operationId = self::buildOperationId($path, $method);
        }

        // create method
        $data = new Table\Generated\RoutesMethodRow([
            Table\Generated\RoutesMethodTable::COLUMN_ROUTE_ID => $routeId,
            Table\Generated\RoutesMethodTable::COLUMN_METHOD => $method,
            Table\Generated\RoutesMethodTable::COLUMN_VERSION => $ver,
            Table\Generated\RoutesMethodTable::COLUMN_STATUS => $status,
            Table\Generated\RoutesMethodTable::COLUMN_ACTIVE => $active ? 1 : 0,
            Table\Generated\RoutesMethodTable::COLUMN_PUBLIC => $public ? 1 : 0,
            Table\Generated\RoutesMethodTable::COLUMN_DESCRIPTION => $description,
            Table\Generated\RoutesMethodTable::COLUMN_OPERATION_ID => $operationId,
            Table\Generated\RoutesMethodTable::COLUMN_PARAMETERS => $parameters,
            Table\Generated\RoutesMethodTable::COLUMN_REQUEST => $request,
            Table\Generated\RoutesMethodTable::COLUMN_ACTION => $action,
            Table\Generated\RoutesMethodTable::COLUMN_COSTS => $costs,
        ]);

        $this->methodTable->create($data);

        return $this->methodTable->getLastInsertId();
    }

    /**
     * @param integer $methodId
     * @param RouteMethod $config
     */
    private function createResponses(int $methodId, RouteMethod $config): void
    {
        $response  = $config->getResponse(); // deprecated
        $responses = $config->getResponses();

        if (!empty($responses)) {
            foreach ($responses as $statusCode => $response) {
                $record = new Table\Generated\RoutesResponseRow([
                    Table\Generated\RoutesResponseTable::COLUMN_METHOD_ID => $methodId,
                    Table\Generated\RoutesResponseTable::COLUMN_CODE => $statusCode,
                    Table\Generated\RoutesResponseTable::COLUMN_RESPONSE => $response,
                ]);

                $this->responseTable->create($record);
            }
        } elseif (!empty($response)) {
            $record = new Table\Generated\RoutesResponseRow([
                Table\Generated\RoutesResponseTable::COLUMN_METHOD_ID => $methodId,
                Table\Generated\RoutesResponseTable::COLUMN_CODE => 200,
                Table\Generated\RoutesResponseTable::COLUMN_RESPONSE => $response,
            ]);

            $this->responseTable->create($record);
        }
    }

    public static function buildOperationId(string $path, string $method): string
    {
        $parts = array_filter(explode('/', $path));

        $parts = array_map(static function(string $part){
            if ($part[0] === ':') {
                return substr($part, 1);
            } elseif ($part[0] === '$') {
                return substr($part, 1, strpos($part, '<') - 1);
            } else {
                return $part;
            }
        }, $parts);

        return strtolower($method) . '.' . implode('.', $parts);
    }
}
