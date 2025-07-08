<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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
    private Service\System\FrameworkConfig $frameworkConfig;

    public function __construct(Processor $processor, Service\Plan\Payer $planPayerService, Service\System\FrameworkConfig $frameworkConfig)
    {
        $this->processor = $processor;
        $this->planPayerService = $planPayerService;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function invoke(RequestInterface $request, Context $context): mixed
    {
        $operation = $context->getOperation();
        $action = $operation->getAction();
        $costs = $operation->getCosts();

        if ($operation->getActive() === 0) {
            throw new StatusCode\GoneException('This action is not longer available');
        }

        $baseUrl = $this->frameworkConfig->getDispatchUrl();
        $context = new EngineContext($operation->getId(), $baseUrl, $context->getApp(), $context->getUser(), $this->frameworkConfig->getTenantId());

        if ($costs > 0) {
            // as anonymous user it is not possible to pay
            if ($context->getUser()->isAnonymous()) {
                throw new StatusCode\ForbiddenException('This action costs points because of this you must be authenticated in order to call this action');
            }

            // in case the method has assigned costs check whether the user has enough points
            if (!$this->planPayerService->canSpent($costs, $context)) {
                throw new StatusCode\PaymentRequiredException('Your account has not enough points to call this action. Please purchase new points in order to execute this action');
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
