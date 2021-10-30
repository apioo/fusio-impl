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

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Action\Invoker as ActionInvoker;
use Fusio\Impl\Table;
use PSX\Http\Exception\StatusCodeException;
use PSX\Json\Rpc\Exception\MethodNotFoundException;
use PSX\Json\Rpc\Exception\ServerErrorException;
use PSX\Record\Record;
use PSX\Schema\Exception\ValidationException;

/**
 * Invoker
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
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
