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

namespace Fusio\Impl\Consumer\Action\Payment;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Payment;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Model\Consumer\PaymentCheckoutRequest;

/**
 * Checkout
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Checkout implements ActionInterface
{
    private Payment $paymentService;
    private ContextFactory $contextFactory;

    public function __construct(Payment $paymentService, ContextFactory $contextFactory)
    {
        $this->paymentService = $paymentService;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof PaymentCheckoutRequest);

        $approvalUrl = $this->paymentService->checkout(
            $request->get('provider'),
            $body,
            $context->getUser(),
            $this->contextFactory->newActionContext($context)
        );

        return [
            'approvalUrl' => $approvalUrl,
        ];
    }
}
