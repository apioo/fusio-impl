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

use Datto\JsonRpc\Exceptions\ApplicationException;
use Datto\JsonRpc\Exceptions\MethodException;
use Fusio\Engine\Context as EngineContext;
use Fusio\Engine\Processor;
use Fusio\Engine\Repository;
use Fusio\Engine\Request;
use Fusio\Impl\Loader\Context;
use Fusio\Impl\Record\PassthruRecord;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Framework\Config\Config;
use PSX\Http\RequestInterface;
use PSX\Record\Record;

/**
 * Evaluator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Evaluator implements \Datto\JsonRpc\Evaluator
{
    const ERROR_INVALID_TOKEN = 1;
    const ERROR_RATE_LIMIT = 2;
    const ERROR_FORBIDDEN = 4;
    const ERROR_NOT_ENOUGH_POINTS = 8;
    const ERROR_NO_ACTION_PROVIDED = 8;

    /**
     * @var \Fusio\Engine\Processor
     */
    protected $processor;

    /**
     * @var \Fusio\Impl\Service\Security\TokenValidator
     */
    protected $tokenValidator;

    /**
     * @var \Fusio\Impl\Service\Rate
     */
    protected $rateService;

    /**
     * @var \Fusio\Impl\Service\Log
     */
    protected $logService;

    /**
     * @var \Fusio\Impl\Service\Plan\Payer
     */
    protected $planPayerService;

    /**
     * @var \Fusio\Impl\Table\Routes\Method 
     */
    protected $methodTable;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @var \PSX\Http\RequestInterface
     */
    protected $request;

    /**
     * @param \Fusio\Engine\Processor $processor
     * @param \Fusio\Impl\Service\Security\TokenValidator $tokenValidator
     * @param \Fusio\Impl\Service\Rate $rateService
     * @param \Fusio\Impl\Service\Log $logService
     * @param \Fusio\Impl\Table\Routes\Method $methodTable
     * @param \PSX\Framework\Config\Config $config
     * @param \PSX\Http\RequestInterface $request
     */
    public function __construct(Processor $processor, Service\Security\TokenValidator $tokenValidator, Service\Rate $rateService, Service\Log $logService, Service\Plan\Payer $planPayerService, Table\Routes\Method $methodTable, Config $config, RequestInterface $request)
    {
        $this->processor        = $processor;
        $this->tokenValidator   = $tokenValidator;
        $this->rateService      = $rateService;
        $this->logService       = $logService;
        $this->planPayerService = $planPayerService;
        $this->methodTable      = $methodTable;
        $this->config           = $config;
        $this->request          = $request;
    }

    /**
     * @inheritDoc
     */
    public function evaluate($method, $arguments)
    {
        $operationId = $method;
        $remoteIp    = $this->request->getAttribute('REMOTE_ADDR') ?: '127.0.0.1';

        $method = $this->methodTable->getMethodByOperationId($operationId);

        if (empty($method)) {
            throw new MethodException();
        }

        $context = new Context();
        $context->setRouteId($method['route_id']);
        $context->setMethod($method);

        $success = $this->tokenValidator->assertAuthorization(
            $method['method'],
            $this->request->getHeader('Authorization'),
            $context
        );

        if (!$success) {
            throw new ApplicationException('Could not authorize request', self::ERROR_FORBIDDEN);
        }

        $success = $this->rateService->assertLimit(
            $remoteIp,
            $context->getRouteId(),
            $context->getApp()
        );

        if (!$success) {
            throw new ApplicationException('Rate limit exceeded', self::ERROR_RATE_LIMIT);
        }

        $this->logService->log(
            $remoteIp,
            $method['method'],
            $this->request->getRequestTarget(),
            $this->request->getHeader('User-Agent'),
            $context,
            $this->request
        );

        try {
            // @TODO validate schema
            /*
            if ($method['parameters']) {
                $this->validateSchema($method['parameters'], $method);
            }

            if ($method['request']) {
                $this->validateSchema($method['request'], $method);
            }
            */

            return $this->executeAction($arguments, $method, $context);
        } catch (\Throwable $e) {
            $this->logService->error($e);

            throw $e;
        } finally {
            $this->logService->finish();
        }
    }

    private function executeAction(array $arguments, $method, Context $context)
    {
        $record  = Record::from($arguments['body'] ?? []);
        $baseUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');
        $context = new EngineContext($method['route_id'], $baseUrl, $context->getApp(), $context->getUser());

        $rpcContext = new RpcContext(
            $method['method'],
            $arguments['headers'] ?? [],
            $arguments['uriFragments'] ?? [],
            $arguments['parameters'] ?? []
        );

        $request  = new Request($rpcContext, $record);
        $response = null;
        $actionId = $method['action'];
        $costs    = (int) $method['costs'];
        $cache    = $method['action_cache'];

        if ($costs > 0) {
            // as anonymous user it is not possible to pay
            if ($context->getUser()->isAnonymous()) {
                throw new ApplicationException('This action costs points because of this you must be authenticated in order to call this action', self::ERROR_FORBIDDEN);
            }

            // in case the method has assigned costs check whether the user has
            // enough points
            $remaining = $context->getUser()->getPoints() - $costs;
            if ($remaining < 0) {
                throw new ApplicationException('Your account has not enough points to call this action. Please purchase new points in order to execute this action', self::ERROR_NOT_ENOUGH_POINTS);
            }

            $this->planPayerService->pay($costs, $context);
        }

        if ($actionId > 0) {
            if ($method['status'] != Resource::STATUS_DEVELOPMENT && !empty($cache)) {
                // if the method is not in dev mode we load the action from the
                // cache
                $this->processor->push(Repository\ActionMemory::fromJson($cache));

                $response = $this->processor->execute($actionId, $request, $context);

                $this->processor->pop();
            } else {
                $response = $this->processor->execute($actionId, $request, $context);
            }
        } else {
            throw new ApplicationException('No action provided', self::ERROR_NO_ACTION_PROVIDED);
        }

        return $response->getBody();
    }
}
