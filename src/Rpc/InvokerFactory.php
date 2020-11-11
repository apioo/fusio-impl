<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
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
