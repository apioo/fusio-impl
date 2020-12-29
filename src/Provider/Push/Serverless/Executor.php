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

namespace Fusio\Impl\Provider\Push\Serverless;

use Fusio\Engine\Serverless\ExecutorInterface;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Framework\Loader\LocationFinder\StaticFinder;
use Psr\Container\ContainerInterface;
use PSX\Engine\DispatchInterface;
use PSX\Framework\Dispatch\Dispatch;
use PSX\Http\Request;
use PSX\Http\ResponseInterface;
use PSX\Http\Server\ResponseFactory;
use PSX\Uri\Uri;

/**
 * Executor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Executor implements ExecutorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Dispatch
     */
    private $dispatcher;

    public function __construct(ContainerInterface $container, DispatchInterface $dispatcher)
    {
        $this->container  = $container;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $method, string $httpMethod, string $path, array $uriFragments, array $headers, ?string $body = null): ResponseInterface
    {
        // we dont need to use the routing of the framework
        $this->container->set('loader_location_finder', new StaticFinder());

        $context = new Context();
        $context->setParameters($uriFragments);
        $context->setPath($path);
        $context->setSource(SchemaApiController::class);
        $context->setRouteId($method['route_id']);
        $context->setCategoryId($method['category_id']);

        $request  = new Request(new Uri($path), $httpMethod, $headers, $body);
        $response = (new ResponseFactory())->createResponse();

        return $this->dispatcher->route($request, $response, $context);
    }
}
