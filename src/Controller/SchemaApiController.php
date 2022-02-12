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

namespace Fusio\Impl\Controller;

use Fusio\Engine\Record\PassthruRecord;
use Fusio\Engine\Request;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Service\Action\Invoker;
use Fusio\Impl\Service\Log;
use Fusio\Impl\Service\Rate;
use Fusio\Impl\Service\Route\Method;
use Fusio\Impl\Service\Security\TokenValidator;
use PSX\Api\Resource\MethodAbstract;
use PSX\Dependency\Attribute\Inject;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Filter\UserAgentEnforcer;
use PSX\Http\RequestInterface;
use PSX\Record\Record;
use PSX\Record\RecordInterface;

/**
 * SchemaApiController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class SchemaApiController extends ControllerAbstract
{
    private const SCHEMA_PASSTHRU = 'Passthru';

    #[Inject]
    private Loader $schemaLoader;

    #[Inject]
    private Method $routesMethodService;

    #[Inject]
    private Rate $rateService;

    #[Inject]
    private Log $logService;

    #[Inject]
    private TokenValidator $securityTokenValidator;

    #[Inject]
    private Invoker $actionInvokerService;

    public function getPreFilter(): array
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

    protected function doGet(HttpContextInterface $context): mixed
    {
        return $this->executeAction(new Record(), $context);
    }

    protected function doPost(mixed $record, HttpContextInterface $context): mixed
    {
        return $this->executeAction($record, $context);
    }

    protected function doPut(mixed $record, HttpContextInterface $context): mixed
    {
        return $this->executeAction($record, $context);
    }

    protected function doPatch(mixed $record, HttpContextInterface $context): mixed
    {
        return $this->executeAction($record, $context);
    }

    protected function doDelete(HttpContextInterface $context): mixed
    {
        return $this->executeAction(new Record(), $context);
    }

    protected function parseRequest(RequestInterface $request, MethodAbstract $method): mixed
    {
        if ($method->hasRequest()) {
            if ($method->getRequest() == self::SCHEMA_PASSTHRU) {
                return new PassthruRecord($this->requestReader->getBody($request));
            } else {
                $schema = $this->schemaLoader->getSchema($method->getRequest());
                return $this->requestReader->getBodyAs($request, $schema);
            }
        } else {
            return new Record();
        }
    }

    private function executeAction(mixed $record, HttpContextInterface $httpContext): mixed
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
