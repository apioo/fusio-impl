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

namespace Fusio\Impl\Tests\Adapter\Test;

use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Engine\Payment\CheckoutContext;
use Fusio\Engine\Payment\ProviderInterface;
use Fusio\Engine\Payment\WebhookInterface;
use PSX\Http\RequestInterface;

/**
 * Paypal
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Paypal implements ProviderInterface
{
    public function checkout(mixed $connection, ProductInterface $product, UserInterface $user, CheckoutContext $context): string
    {
        $approvalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-60385559L1062554J';

        return $approvalUrl;
    }

    public function webhook(RequestInterface $request, WebhookInterface $handler, ?string $webhookSecret = null, ?string $domain = null): void
    {
    }

    public function portal(mixed $connection, UserInterface $user, string $returnUrl, ?string $configurationId = null): ?string
    {
        return 'https://paypal.com';
    }
}
