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

namespace Fusio\Impl\System\Action\Payment;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service;
use PSX\Framework\Environment\IPResolver;

/**
 * Webhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Webhook implements ActionInterface
{
    public function __construct(
        private Service\Log $logService,
        private Service\Payment $paymentService,
        private ContextFactory $contextFactory,
        private IPResolver $ipResolver,
    ) {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $requestContext = $request->getContext();
        if (!$requestContext instanceof HttpRequestContext) {
            throw new \RuntimeException('Invoking the webhook is currently only supported through HTTP');
        }

        $httpRequest = $requestContext->getRequest();

        $this->logService->log(
            $this->ipResolver->resolveByRequest($httpRequest),
            $httpRequest->getMethod(),
            $httpRequest->getRequestTarget(),
            $httpRequest->getHeader('User-Agent'),
            $this->contextFactory->getActive(),
            $httpRequest
        );

        $provider = $requestContext->getParameter('provider');
        if (empty($provider)) {
            throw new \RuntimeException('No provider provided');
        }

        try {
            $this->paymentService->webhook($provider, $httpRequest);
        } catch (\Throwable $e) {
            $this->logService->error($e);

            throw $e;
        } finally {
            $this->logService->finish(200);
        }

        return [
            'success' => true,
            'message' => 'Execution successful'
        ];
    }
}
