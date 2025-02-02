<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Framework\Filter;

use Fusio\Impl\Controller\ActionController;
use Fusio\Impl\Framework\Loader\Context as FusioContext;
use PSX\Framework\Filter\ControllerExecutorFactoryInterface;
use PSX\Framework\Http\RequestReader;
use PSX\Framework\Http\ResponseWriter;
use PSX\Framework\Loader\Context;
use PSX\Http\FilterInterface;
use PSX\Schema\SchemaManagerInterface;

/**
 * ActionExecutorFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActionExecutorFactory implements ControllerExecutorFactoryInterface
{
    private SchemaManagerInterface $schemaManager;
    private RequestReader $requestReader;
    private ResponseWriter $responseWriter;

    public function __construct(SchemaManagerInterface $schemaManager, RequestReader $requestReader, ResponseWriter $responseWriter)
    {
        $this->schemaManager = $schemaManager;
        $this->requestReader = $requestReader;
        $this->responseWriter = $responseWriter;
    }

    public function factory(object $controller, string $methodName, Context $context): FilterInterface
    {
        if (!$controller instanceof ActionController) {
            throw new \RuntimeException('Provided an invalid controller');
        }

        if (!$context instanceof FusioContext) {
            throw new \RuntimeException('Provided an invalid context');
        }

        return new ActionExecutor($controller, $context, $this->schemaManager, $this->requestReader, $this->responseWriter);
    }
}
