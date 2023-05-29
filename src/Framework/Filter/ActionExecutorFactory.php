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

use Fusio\Impl\Controller\ActionController;
use Fusio\Impl\Framework\Loader\Context as FusioContext;
use Fusio\Impl\Service\Schema\Loader;
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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
