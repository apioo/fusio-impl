<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Rpc;

use Fusio\Engine\Request\HttpRequest;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Action\Invoker as ActionInvoker;
use Fusio\Impl\Table;
use PSX\Http\RequestInterface;
use PSX\Schema\SchemaTraverser;

/**
 * InvokerFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InvokerFactory
{
    /**
     * @var \Fusio\Impl\Service\Action\Invoker
     */
    private $actionInvoker;

    /**
     * @var \Fusio\Impl\Table\Route\Method
     */
    private $methodTable;

    /**
     * @var \Fusio\Impl\Schema\Loader
     */
    private $schemaLoader;

    /**
     * @var \Fusio\Impl\Service\Security\TokenValidator
     */
    private $tokenValidator;

    /**
     * @var \Fusio\Impl\Service\Rate
     */
    private $rateService;

    /**
     * @var \PSX\Schema\SchemaTraverser
     */
    private $schemaTraverser;

    /**
     * @param ActionInvoker $actionInvoker
     * @param Table\Route\Method $methodTable
     * @param Loader $schemaLoader
     * @param Service\Security\TokenValidator $tokenValidator
     * @param Service\Rate $rateService
     */
    public function __construct(ActionInvoker $actionInvoker, Table\Route\Method $methodTable, Loader $schemaLoader, Service\Security\TokenValidator $tokenValidator, Service\Rate $rateService)
    {
        $this->actionInvoker    = $actionInvoker;
        $this->methodTable      = $methodTable;
        $this->schemaLoader     = $schemaLoader;
        $this->tokenValidator   = $tokenValidator;
        $this->rateService      = $rateService;
        $this->schemaTraverser  = new SchemaTraverser();
    }

    public function createByFramework(RequestInterface $request): Invoker
    {
        $invoker = new Invoker($this->actionInvoker, $this->methodTable);
        $invoker->addMiddleware(new Middleware\Authentication($this->tokenValidator, $request->getHeader('Authorization')));
        $invoker->addMiddleware(new Middleware\RequestLimit($this->rateService, $request->getAttribute('REMOTE_ADDR') ?: '127.0.0.1'));
        $invoker->addMiddleware(new Middleware\ValidateSchema($this->schemaLoader));

        return $invoker;
    }

    public function createByEngine(HttpRequest $request): Invoker
    {
        $invoker = new Invoker($this->actionInvoker, $this->methodTable);
        $invoker->addMiddleware(new Middleware\Authentication($this->tokenValidator, $request->getHeader('Authorization')));
        $invoker->addMiddleware(new Middleware\RequestLimit($this->rateService, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
        $invoker->addMiddleware(new Middleware\ValidateSchema($this->schemaLoader));

        return $invoker;
    }
}
