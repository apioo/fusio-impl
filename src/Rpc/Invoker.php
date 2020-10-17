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

use Fusio\Engine\Context as EngineContext;
use Fusio\Engine\Processor;
use Fusio\Engine\Repository;
use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;
use PSX\Http\Exception\StatusCodeException;
use PSX\Http\RequestInterface;
use PSX\Json\Rpc\Exception\MethodNotFoundException;
use PSX\Json\Rpc\Exception\ServerErrorException;
use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Schema\SchemaInterface;
use PSX\Schema\SchemaTraverser;
use PSX\Schema\ValidationException;
use PSX\Schema\Visitor\TypeVisitor;

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
     * @var \Fusio\Engine\Processor
     */
    private $processor;

    /**
     * @var \Fusio\Impl\Service\Plan\Payer
     */
    private $planPayerService;

    /**
     * @var \Fusio\Impl\Table\Route\Method 
     */
    private $methodTable;

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @param Processor $processor
     * @param Service\Plan\Payer $planPayerService
     * @param Table\Route\Method $methodTable
     */
    public function __construct(Processor $processor, Service\Plan\Payer $planPayerService, Table\Route\Method $methodTable)
    {
        $this->processor        = $processor;
        $this->planPayerService = $planPayerService;
        $this->methodTable      = $methodTable;
        $this->middlewares      = [];
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
            throw new ServerErrorException($e->getMessage(), $e->getStatusCode());
        } catch (ValidationException $e) {
            throw new ServerErrorException($e->getMessage(), 400);
        } catch (\Throwable $e) {
            throw new ServerErrorException($e->getMessage(), 500);
        }
    }

    private function execute($operationId, $arguments)
    {
        $method = $this->methodTable->getMethodByOperationId($operationId);
        if (empty($method)) {
            throw new MethodNotFoundException('Method not found');
        }

        $context = new Context();
        $context->setRouteId($method['route_id']);
        $context->setMethod($method);

        $arguments = Record::from($arguments);

        foreach ($this->middlewares as $middleware) {
            $middleware($arguments, $method, $context);
        }

        return $this->executeAction($operationId, $arguments, $method, $context);
    }

    private function executeAction($operationId, RecordInterface $arguments, $method, Context $context)
    {
        $context  = new EngineContext($method['route_id'], '', $context->getApp(), $context->getUser());
        $request  = new Request\RpcRequest($operationId, $arguments);
        $response = null;
        $actionId = $method['action'];
        $costs    = (int) $method['costs'];

        if ($costs > 0) {
            // as anonymous user it is not possible to pay
            if ($context->getUser()->isAnonymous()) {
                throw new StatusCode\ForbiddenException('This action costs points because of this you must be authenticated in order to call this action');
            }

            // in case the method has assigned costs check whether the user has
            // enough points
            $remaining = $context->getUser()->getPoints() - $costs;
            if ($remaining < 0) {
                throw new StatusCode\ClientErrorException('Your account has not enough points to call this action. Please purchase new points in order to execute this action', 429);
            }

            $this->planPayerService->pay($costs, $context);
        }

        if ($actionId > 0) {
            $response = $this->processor->execute($actionId, $request, $context);
        } else {
            throw new StatusCode\ServiceUnavailableException('No action provided');
        }

        return $response->getBody();
    }
}
