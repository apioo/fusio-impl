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

use PSX\Api\ApiManagerInterface;
use PSX\Framework\Filter\ControllerExecutor;
use PSX\Framework\Http\RequestReader;
use PSX\Framework\Http\ResponseWriter;
use PSX\Framework\Loader\Context;
use PSX\Http\FilterInterface;

/**
 * ControllerExecutor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ControllerExecutorFactory
{
    private RequestReader $requestReader;
    private ResponseWriter $responseWriter;
    private ApiManagerInterface $apiManager;

    public function __construct(RequestReader $requestReader, ResponseWriter $responseWriter, ApiManagerInterface $apiManager)
    {
        $this->requestReader = $requestReader;
        $this->responseWriter = $responseWriter;
        $this->apiManager = $apiManager;
    }

    public function factory(object $controller, string $methodName, Context $context): FilterInterface
    {
        return new ControllerExecutor(
            $controller,
            $methodName,
            $context,
            $this->requestReader,
            $this->responseWriter,
            $this->apiManager
        );
    }
}
