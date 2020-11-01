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

namespace Fusio\Impl\Service\Route;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Route_Method;
use Fusio\Impl\Backend\Model\Route_Version;
use Fusio\Impl\Framework\Filter\Filter;
use Fusio\Impl\Table;
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
 * @link    http://fusio-project.org
 */
class Config
{
    /**
     * @var \Fusio\Impl\Table\Route\Method
     */
    protected $methodTable;

    /**
     * @var \Fusio\Impl\Table\Route\Response
     */
    protected $responseTable;

    /**
     * @var \PSX\Api\ListingInterface
     */
    protected $listing;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Route\Method $methodTable
     * @param \Fusio\Impl\Table\Route\Response $responseTable
     * @param \PSX\Api\ListingInterface $listing
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Route\Method $methodTable, Table\Route\Response $responseTable, ListingInterface $listing, EventDispatcherInterface $eventDispatcher)
    {
        $this->methodTable     = $methodTable;
        $this->responseTable   = $responseTable;
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
     * @param Route_Version[] $versions
     */
    public function handleConfig(int $routeId, string $path, array $versions, UserContext $context)
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
                    $this->listing->invalidateResource($path, $ver);
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
                    $this->methodTable->update([
                        'id'     => $existingMethod['id'],
                        'status' => $status,
                    ]);
                }
            }
        }

        // invalidate resource cache
        if ($this->listing instanceof CachedListing) {
            $this->listing->invalidateResourceIndex(new Filter('default'));
            $this->listing->invalidateResourceCollection(null, new Filter('default'));
            $this->listing->invalidateResource($path);
        }
    }

    /**
     * @param integer $routeId
     * @param string $method
     * @param integer $ver
     * @param integer $status
     * @param Route_Method $config
     * @param string $path
     * @return int
     */
    private function createMethod(int $routeId, string $method, int $ver, int $status, Route_Method $config, string $path)
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
        $data = [
            'route_id'     => $routeId,
            'method'       => $method,
            'version'      => $ver,
            'status'       => $status,
            'active'       => $active ? 1 : 0,
            'public'       => $public ? 1 : 0,
            'description'  => $description,
            'operation_id' => $operationId,
            'parameters'   => $parameters,
            'request'      => $request,
            'action'       => $action,
            'costs'        => $costs,
        ];

        $this->methodTable->create($data);

        return $this->methodTable->getLastInsertId();
    }

    /**
     * @param integer $methodId
     * @param Route_Method $config
     */
    private function createResponses(int $methodId, Route_Method $config)
    {
        $response  = $config->getResponse(); // deprecated
        $responses = $config->getResponses();

        if (!empty($responses)) {
            foreach ($responses as $statusCode => $response) {
                $this->responseTable->create([
                    'method_id' => $methodId,
                    'code'      => $statusCode,
                    'response'  => $response,
                ]);
            }
        } elseif (!empty($response)) {
            $this->responseTable->create([
                'method_id' => $methodId,
                'code'      => 200,
                'response'  => $response,
            ]);
        }
    }

    public static function buildOperationId(string $path, string $method)
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
