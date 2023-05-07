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

namespace Fusio\Impl\Framework\Filter;

use Fusio\Engine\Request;
use Fusio\Impl\Controller\ActionController;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Schema\Loader;
use PSX\Framework\Http\RequestReader;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\Environment\HttpContext;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Record\Record;

/**
 * ControllerExecutor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ActionExecutor implements FilterInterface
{
    private ActionController $controller;
    private Context $context;
    private Loader $schemaLoader;
    private RequestReader $requestReader;
    private ResponseWriter $responseWriter;

    public function __construct(ActionController $controller, Context $context, Loader $schemaLoader, RequestReader $requestReader, ResponseWriter $responseWriter)
    {
        $this->controller = $controller;
        $this->context = $context;
        $this->schemaLoader = $schemaLoader;
        $this->requestReader = $requestReader;
        $this->responseWriter = $responseWriter;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        if ($request->getMethod() === 'OPTIONS') {
            $filterChain->handle($request, $response);
            return;
        }

        $arguments = array_merge($request->getUri()->getParameters(), $this->context->getParameters());

        $context = new HttpContext($request, $this->context->getParameters());
        $requestContext = new Request\HttpRequest($context);

        $method = $this->context->getMethod();
        if (!empty($method['request']) && in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            $schema = $this->schemaLoader->getSchema($method['request']);
            $payload = $this->requestReader->getBodyAs($request, $schema);
        } else {
            $payload = new Record();
        }

        $result = $this->controller->execute(new Request($arguments, $payload, $requestContext), $this->context);

        $this->responseWriter->setBody($response, $result, $request);

        $filterChain->handle($request, $response);
    }
}
