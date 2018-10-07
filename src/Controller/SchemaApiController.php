<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Controller;

use Fusio\Engine\Context as EngineContext;
use Fusio\Engine\Repository;
use Fusio\Engine\Request;
use Fusio\Impl\Export;
use Fusio\Impl\Filter\AssertMethod;
use Fusio\Impl\Filter\Authentication;
use Fusio\Impl\Filter\Logger;
use Fusio\Impl\Filter\RequestLimit;
use Fusio\Impl\Record\PassthruRecord;
use PSX\Api\DocumentedInterface;
use PSX\Api\Resource;
use PSX\Api\Resource\MethodAbstract;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Http\Filter\UserAgentEnforcer;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Record\Record;

/**
 * SchemaApiController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SchemaApiController extends SchemaApiAbstract implements DocumentedInterface
{
    const SCHEMA_PASSTHRU = 'passthru';

    /**
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    /**
     * @Inject
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @Inject
     * @var \Fusio\Engine\Processor
     */
    protected $processor;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Routes\Method
     */
    protected $routesMethodService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Rate
     */
    protected $rateService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Plan\Payer
     */
    protected $planPayerService;

    /**
     * @Inject
     * @var \Fusio\Engine\Repository\AppInterface
     */
    protected $appRepository;

    /**
     * @Inject
     * @var \Fusio\Engine\Repository\UserInterface
     */
    protected $userRepository;

    /**
     * @return array
     */
    public function getPreFilter()
    {
        $filter = [];

        // it is required for every request to have an user agent which
        // identifies the client
        $filter[] = new UserAgentEnforcer();

        $filter[] = new AssertMethod(
            $this->routesMethodService,
            $this->context
        );

        $filter[] = new Authentication(
            $this->connection,
            $this->context,
            $this->config->get('fusio_project_key'),
            $this->appRepository,
            $this->userRepository
        );

        $filter[] = new RequestLimit(
            $this->rateService,
            $this->appRepository,
            $this->context
        );

        $filter[] = new Logger(
            $this->connection,
            $this->context
        );

        return $filter;
    }

    /**
     * Select all methods from the routes method table and build a resource
     * based on the data. If the route is in production mode read the schema
     * from the cache else resolve it
     *
     * @param integer $version
     * @return \PSX\Api\Resource|null
     */
    public function getDocumentation($version = null)
    {
        return $this->routesMethodService->getDocumentation(
            $this->context->getRouteId(),
            $version,
            $this->context->getPath()
        );
    }

    /**
     * @inheritdoc
     */
    public function onOptions(RequestInterface $request, ResponseInterface $response)
    {
        parent::onOptions($request, $response);

        $methods = $this->routesMethodService->getRequestSchemas($this->context->getRouteId(), '*');
        foreach ($methods as $methodName => $schemaId) {
            $url = $this->reverseRouter->getUrl(Export\Api\Schema::class, ['name' => $schemaId]);
            $response->addHeader('Link', '<' . $url . '>; rel="' . strtolower($methodName) . '-schema"');
        }
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return $this->executeAction(new Record(), $context);
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        return $this->executeAction($record, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        return $this->executeAction($record, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doPatch($record, HttpContextInterface $context)
    {
        return $this->executeAction($record, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        return $this->executeAction($record, $context);
    }

    /**
     * @inheritdoc
     */
    protected function parseRequest(RequestInterface $request, MethodAbstract $method)
    {
        if ($method->hasRequest()) {
            if ($method->getRequest()->getDefinition()->getTitle() == self::SCHEMA_PASSTHRU) {
                return new PassthruRecord($this->requestReader->getBody($request));
            } else {
                return $this->requestReader->getBodyAs($request, $method->getRequest(), $this->getValidator($method));
            }
        } else {
            return new Record();
        }
    }

    /**
     * @param mixed $record
     * @param \PSX\Http\Environment\HttpContextInterface $httpContext
     * @return \PSX\Http\Environment\HttpResponseInterface|null
     */
    private function executeAction($record, HttpContextInterface $httpContext)
    {
        $baseUrl  = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');
        $context  = new EngineContext($this->context->getRouteId(), $baseUrl, $this->context->getApp(), $this->context->getUser());

        $request  = new Request($httpContext, $record);
        $response = null;
        $method   = $this->context->getMethod();
        $actionId = $method['action'];
        $costs    = (int) $method['costs'];
        $cache    = $method['action_cache'];

        if ($costs > 0) {
            // as anonymous user it is not possible to pay
            if ($this->context->getUser()->isAnonymous()) {
                throw new StatusCode\ForbiddenException('This action costs points because of this you must be authenticated in order to call this action');
            }

            // in case the method has assigned costs check whether the user has
            // enough points
            $remaining = $this->context->getUser()->getPoints() - $costs;
            if ($remaining < 0) {
                throw new StatusCode\ClientErrorException('Your account has not enough points to call this action. Please purchase new points in order to execute this action', 429);
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
            throw new StatusCode\ServiceUnavailableException('No action provided');
        }

        return $response;
    }
}
