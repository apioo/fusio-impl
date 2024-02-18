<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Action;

use Fusio\Engine\Context as EngineContext;
use Fusio\Engine\Processor;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Invoker
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Invoker
{
    private Processor $processor;
    private Service\Plan\Payer $planPayerService;
    private ConfigInterface $config;

    public function __construct(Processor $processor, Service\Plan\Payer $planPayerService, ConfigInterface $config)
    {
        $this->processor = $processor;
        $this->planPayerService = $planPayerService;
        $this->config = $config;
    }

    public function invoke(RequestInterface $request, Context $context): mixed
    {
        $operation = $context->getOperation();
        $action = $operation->getAction();
        $costs = $operation->getCosts();

        $baseUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');
        $context = new EngineContext($operation->getId(), $baseUrl, $context->getApp(), $context->getUser(), $this->config->get('fusio_tenant_id'));

        if ($costs > 0) {
            // as anonymous user it is not possible to pay
            if ($context->getUser()->isAnonymous()) {
                throw new StatusCode\ForbiddenException('This action costs points because of this you must be authenticated in order to call this action');
            }

            // in case the method has assigned costs check whether the user has enough points
            $remaining = $context->getUser()->getPoints() - $costs;
            if ($remaining < 0) {
                throw new StatusCode\ClientErrorException('Your account has not enough points to call this action. Please purchase new points in order to execute this action', 429);
            }

            $this->planPayerService->pay($costs, $context);
        }

        if (!empty($action)) {
            $response = $this->processor->execute($action, $request, $context);
        } else {
            throw new StatusCode\ServiceUnavailableException('No action provided');
        }

        return $response;
    }
}
