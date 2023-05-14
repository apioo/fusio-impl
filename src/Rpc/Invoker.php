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

namespace Fusio\Impl\Rpc;

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Action\Invoker as ActionInvoker;
use Fusio\Impl\Table;
use PSX\Http\Exception\StatusCodeException;
use PSX\Json\Rpc\Exception\MethodNotFoundException;
use PSX\Json\Rpc\Exception\ServerErrorException;
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
    private ActionInvoker $actionInvoker;
    private Table\Route\Method $methodTable;
    private array $middlewares;

    public function __construct(ActionInvoker $actionInvoker, Table\Route\Method $methodTable)
    {
        $this->actionInvoker = $actionInvoker;
        $this->methodTable   = $methodTable;
        $this->middlewares   = [];
    }

    public function addMiddleware(callable $middleware): void
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

    private function execute(string $operationId, mixed $arguments)
    {
        $method = $this->methodTable->getMethodByOperationId($operationId);
        if (empty($method)) {
            throw new MethodNotFoundException('Method not found');
        }

        $context = new Context();
        $context->setOperationId((int) $method['route_id']);
        $context->setOperation($method);

        if (!$arguments instanceof \stdClass) {

        }

        $payload = $arguments->payload ?? null;
        $request = new Request($arguments, $payload, new Request\RpcRequest($operationId));

        foreach ($this->middlewares as $middleware) {
            $middleware($request, $context);
        }

        $response = $this->actionInvoker->invoke($request, $context);

        return $response->getBody();
    }
}
