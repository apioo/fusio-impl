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

namespace Fusio\Impl\Controller\Filter;

use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service;
use PSX\Framework\Loader\ContextFactoryInterface;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

/**
 * AssertMethod
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class AssertMethod implements FilterInterface
{
    private Service\Route\Method $routesMethodService;
    private ContextFactory $contextFactory;

    public function __construct(Service\Route\Method $routesMethodService, ContextFactory $contextFactory)
    {
        $this->routesMethodService = $routesMethodService;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        $context    = $this->contextFactory->getActive();
        $routeId    = $context->getRouteId();
        $methodName = $request->getMethod();

        if ($methodName === 'HEAD') {
            // in case of HEAD we use the schema of the GET request
            $methodName = 'GET';
        } elseif ($methodName === 'OPTIONS') {
            // for OPTIONS request we dont need method details
            $filterChain->handle($request, $response);
            return;
        }

        $version = $this->getSubmittedVersionNumber($request);
        $method  = $this->routesMethodService->getMethod($routeId, $version, $methodName);

        if (empty($method)) {
            $methods = $this->routesMethodService->getAllowedMethods($routeId, $version);
            $allowed = ['OPTIONS'];
            if (in_array('GET', $methods)) {
                $allowed[] = 'HEAD';
            }

            $allowedMethods = array_merge($allowed, $methods);

            throw new StatusCode\MethodNotAllowedException('Given request method is not supported', $allowedMethods);
        }

        $context->setMethod($method);

        // add request id
        $request->setHeader('X-Request-Id', Uuid::pseudoRandom());

        $filterChain->handle($request, $response);
    }

    /**
     * Returns the version number which was submitted by the client in the accept header field
     */
    private function getSubmittedVersionNumber(RequestInterface $request): ?string
    {
        $accept  = $request->getHeader('Accept');
        $matches = array();

        preg_match('/^application\/vnd\.([a-z.-_]+)\.v([\d]+)\+([a-z]+)$/', $accept, $matches);

        return $matches[2] ?? null;
    }
}
