<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service;

use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Model\Product;
use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Engine\Payment\CheckoutContext;
use Fusio\Engine\Payment\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\PaymentProvider;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Payment\Webhook;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\PaymentCheckoutRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Http\RequestInterface;

/**
 * Payment
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Payment
{
    private ConnectorInterface $connector;
    private PaymentProvider $paymentProvider;
    private Webhook $webhook;
    private Service\Config $configService;
    private Service\System\FrameworkConfig $frameworkConfig;
    private Table\Plan $planTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(ConnectorInterface $connector, PaymentProvider $paymentProvider, Webhook $webhook, Service\Config $configService, Service\System\FrameworkConfig $frameworkConfig, Table\Plan $planTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->connector = $connector;
        $this->paymentProvider = $paymentProvider;
        $this->webhook = $webhook;
        $this->configService = $configService;
        $this->frameworkConfig = $frameworkConfig;
        $this->planTable = $planTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function checkout(string $name, PaymentCheckoutRequest $checkout, UserInterface $user, UserContext $context): string
    {
        $provider = $this->paymentProvider->getInstance($name);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $planId = $checkout->getPlanId();
        if (empty($planId)) {
            throw new StatusCode\BadRequestException('No plan id provided');
        }

        $product = $this->getProduct($context->getTenantId(), $planId);
        $connection = $this->connector->getConnection($name);

        // validate return url
        $returnUrl = $checkout->getReturnUrl();
        if (empty($returnUrl) || !filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            throw new StatusCode\BadRequestException('Invalid return url');
        }

        return $provider->checkout(
            $connection,
            $product,
            $user,
            $this->buildCheckoutContext($returnUrl)
        );
    }

    public function webhook(string $name, RequestInterface $request): void
    {
        $provider = $this->paymentProvider->getInstance($name);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $webhookSecret = $this->configService->getValue('payment_' . strtolower($name) . '_secret');

        $provider->webhook($request, $this->webhook, $webhookSecret, $this->frameworkConfig->getUrl());
    }

    public function portal(string $name, UserInterface $user, string $returnUrl): ?string
    {
        $provider = $this->paymentProvider->getInstance($name);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $configurationId = $this->configService->getValue('payment_' . strtolower($name) . '_portal_configuration');

        $connection = $this->connector->getConnection($name);

        return $provider->portal($connection, $user, $returnUrl, $configurationId);
    }

    private function getProduct(?string $tenantId, int $planId): ProductInterface
    {
        $plan = $this->planTable->findOneByTenantAndId($tenantId, $planId);
        if (!$plan instanceof Table\Generated\PlanRow) {
            throw new StatusCode\BadRequestException('Invalid plan id');
        }

        return new Product(
            $plan->getId(),
            $plan->getName(),
            $plan->getPrice(),
            $plan->getPoints(),
            $plan->getPeriodType() ?? ProductInterface::INTERVAL_ONETIME,
            $plan->getExternalId()
        );
    }

    private function buildCheckoutContext(string $returnUrl): CheckoutContext
    {
        $currency = $this->configService->getValue('payment_currency');
        if (empty($currency)) {
            $currency = 'EUR';
        }

        return new CheckoutContext(
            $returnUrl,
            $returnUrl,
            $currency,
            $this->frameworkConfig->getUrl()
        );
    }
}
