<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\Record\PassthruRecord;
use Fusio\Engine\Request;
use PSX\Api\DocumentedInterface;
use PSX\Api\Resource\MethodAbstract;
use PSX\Api\SpecificationInterface;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Filter\UserAgentEnforcer;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Record\Record;
use PSX\Record\RecordInterface;

/**
 * SchemaApiController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SchemaApiController extends SchemaApiAbstract implements DocumentedInterface
{
    private const SCHEMA_PASSTHRU = 'Passthru';

    /**
     * @var \Fusio\Impl\Framework\Loader\Context
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
     * @var \Fusio\Impl\Schema\Loader
     */
    protected $schemaLoader;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Route\Method
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
     * @var \Fusio\Impl\Service\Log
     */
    protected $logService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Security\TokenValidator
     */
    protected $securityTokenValidator;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Action\Invoker
     */
    protected $actionInvokerService;

    /**
     * @return array
     */
    public function getPreFilter()
    {
        $filter = parent::getPreFilter();

        $filter[] = new UserAgentEnforcer();

        $filter[] = new Filter\AssertMethod(
            $this->routesMethodService,
            $this->context
        );

        $filter[] = new Filter\Authentication(
            $this->securityTokenValidator,
            $this->context
        );

        $filter[] = new Filter\RequestLimit(
            $this->rateService,
            $this->context
        );

        $filter[] = new Filter\Logger(
            $this->logService,
            $this->context
        );

        return $filter;
    }

    /**
     * Select all methods from the routes method table and build a resource
     * based on the data. If the route is in production mode read the schema
     * from the cache else resolve it
     *
     * @param string|null $version
     * @return SpecificationInterface
     */
    public function getDocumentation(string $version = null): ?SpecificationInterface
    {
        return $this->routesMethodService->getDocumentation(
            $this->context->getRouteId(),
            $this->context->getPath(),
            $version
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
            $url = $this->config->get('psx_url') . $this->config->get('psx_dispatch') . '/system/schema/' . $schemaId;
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
            if ($method->getRequest() == self::SCHEMA_PASSTHRU) {
                return new PassthruRecord($this->requestReader->getBody($request));
            } else {
                $schema = $this->schemaLoader->getSchema($method->getRequest());
                return $this->requestReader->getBodyAs($request, $schema, $this->getValidator($method));
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
        if (!$record instanceof RecordInterface) {
            // in case the record is not an RecordInterface, this means the
            // schema traverser has produced a different instance we put the
            // result into the passthru record so the action can access the raw
            // request object
            $record = new PassthruRecord($record);
        }

        $request = new Request\HttpRequest($httpContext, $record);

        return $this->actionInvokerService->invoke($request, $this->context);
    }
}
