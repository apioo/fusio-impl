<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Filter;

use Fusio\Impl\Loader\Context;
use Fusio\Impl\Service;
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
 * @link    http://fusio-project.org
 */
class AssertMethod implements FilterInterface
{
    /**
     * @var \Fusio\Impl\Service\Routes\Method
     */
    protected $routesMethodService;

    /**
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    /**
     * @param \Fusio\Impl\Service\Routes\Method $routesMethodService
     * @param \Fusio\Impl\Loader\Context $context
     */
    public function __construct(Service\Routes\Method $routesMethodService, Context $context)
    {
        $this->routesMethodService = $routesMethodService;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain)
    {
        $routeId    = $this->context->getRouteId();
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

        $this->context->setMethod($method);

        $filterChain->handle($request, $response);
    }

    /**
     * Returns the version number which was submitted by the client in the
     * accept header field
     *
     * @return integer
     */
    private function getSubmittedVersionNumber(RequestInterface $request)
    {
        $accept  = $request->getHeader('Accept');
        $matches = array();

        preg_match('/^application\/vnd\.([a-z.-_]+)\.v([\d]+)\+([a-z]+)$/', $accept, $matches);

        return isset($matches[2]) ? $matches[2] : null;
    }
}
