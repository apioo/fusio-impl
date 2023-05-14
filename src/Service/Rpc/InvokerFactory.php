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

namespace Fusio\Impl\Service\Rpc;

use Fusio\Engine\Request\HttpRequest;
use Fusio\Impl\Rpc\Invoker;
use Fusio\Impl\Rpc\Middleware;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Action\Invoker as ActionInvoker;
use Fusio\Impl\Table;
use PSX\Http\Environment\HttpContextInterface;

/**
 * InvokerFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InvokerFactory
{
    private ActionInvoker $actionInvoker;
    private Table\Operation $operationTable;
    private Service\Schema\Loader $schemaLoader;
    private Service\Security\TokenValidator $tokenValidator;
    private Service\Rate $rateService;

    public function __construct(ActionInvoker $actionInvoker, Table\Operation $operationTable, Service\Schema\Loader $schemaLoader, Service\Security\TokenValidator $tokenValidator, Service\Rate $rateService)
    {
        $this->actionInvoker  = $actionInvoker;
        $this->operationTable    = $operationTable;
        $this->schemaLoader   = $schemaLoader;
        $this->tokenValidator = $tokenValidator;
        $this->rateService    = $rateService;
    }

    public function createByFramework(HttpContextInterface $context): Invoker
    {
        $invoker = new Invoker($this->actionInvoker, $this->operationTable);
        $invoker->addMiddleware(new Middleware\Authentication($this->tokenValidator, $context->getHeader('Authorization')));
        $invoker->addMiddleware(new Middleware\RequestLimit($this->rateService, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
        $invoker->addMiddleware(new Middleware\ValidateSchema($this->schemaLoader));

        return $invoker;
    }

    public function createByEngine(HttpRequest $request): Invoker
    {
        $invoker = new Invoker($this->actionInvoker, $this->operationTable);
        $invoker->addMiddleware(new Middleware\Authentication($this->tokenValidator, $request->getHeader('Authorization')));
        $invoker->addMiddleware(new Middleware\RequestLimit($this->rateService, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
        $invoker->addMiddleware(new Middleware\ValidateSchema($this->schemaLoader));

        return $invoker;
    }
}
