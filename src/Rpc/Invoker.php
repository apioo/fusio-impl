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

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Action\Invoker as ActionInvoker;
use Fusio\Impl\Table;
use PSX\Http\Exception\StatusCodeException;
use PSX\Json\Rpc\Exception\MethodNotFoundException;
use PSX\Json\Rpc\Exception\ServerErrorException;
use PSX\Record\Record;
use PSX\Schema\ValidationException;

/**
 * Invoker
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Invoker
{
    /**
     * @var ActionInvoker
     */
    private $actionInvoker;

    /**
     * @var \Fusio\Impl\Table\Route\Method 
     */
    private $methodTable;

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @param ActionInvoker $actionInvoker
     * @param Table\Route\Method $methodTable
     */
    public function __construct(ActionInvoker $actionInvoker, Table\Route\Method $methodTable)
    {
        $this->actionInvoker = $actionInvoker;
        $this->methodTable   = $methodTable;
        $this->middlewares   = [];
    }

    public function addMiddleware(callable $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function invoke($method, $arguments)
    {
        return $this->__invoke($method, $arguments);
    }

    public function __invoke($method, $arguments)
    {
        try {
            return $this->execute($method, $arguments);
        } catch (StatusCodeException $e) {
            throw new ServerErrorException($e->getMessage(), $e->getStatusCode(), $e);
        } catch (ValidationException $e) {
            throw new ServerErrorException($e->getMessage(), 400, $e);
        } catch (\Throwable $e) {
            throw new ServerErrorException($e->getMessage(), 500, $e);
        }
    }

    private function execute($operationId, $arguments)
    {
        $method = $this->methodTable->getMethodByOperationId($operationId);
        if (empty($method)) {
            throw new MethodNotFoundException('Method not found');
        }

        $context = new Context();
        $context->setRouteId((int) $method['route_id']);
        $context->setMethod($method);

        $request = new Request\RpcRequest($operationId, Record::from($arguments));

        foreach ($this->middlewares as $middleware) {
            $middleware($request, $context);
        }

        $response = $this->actionInvoker->invoke($request, $context);

        return $response->getBody();
    }
}
