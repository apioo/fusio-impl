<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service;

use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Model\Product;
use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Engine\Payment\CheckoutContext;
use Fusio\Engine\Payment\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\PaymentProvider;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Payment\Webhook;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\PaymentCheckoutRequest;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;
use PSX\Http\RequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Payment
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Payment
{
    private ConnectorInterface $connector;
    private PaymentProvider $paymentProvider;
    private Webhook $webhook;
    private Service\Config $configService;
    private Table\Plan $planTable;
    private Config $config;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(ConnectorInterface $connector, PaymentProvider $paymentProvider, Webhook $webhook, Service\Config $configService, Table\Plan $planTable, Config $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->connector = $connector;
        $this->paymentProvider = $paymentProvider;
        $this->webhook = $webhook;
        $this->configService = $configService;
        $this->planTable = $planTable;
        $this->config = $config;
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

        $product    = $this->getProduct($planId);
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

        $provider->webhook($request, $this->webhook, $webhookSecret, $this->config->get('psx_url'));
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

    private function getProduct(int $planId): ProductInterface
    {
        $plan = $this->planTable->find($planId);
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
            $this->config->get('psx_url')
        );
    }
}
