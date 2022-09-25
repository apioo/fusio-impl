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
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Paypal implements ProviderInterface
{
    public function checkout(mixed $connection, ProductInterface $product, UserInterface $user, CheckoutContext $context): string
    {
        $approvalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-60385559L1062554J';

        return $approvalUrl;
    }

    public function webhook(RequestInterface $request, WebhookInterface $handler, ?string $webhookSecret = null): void
    {
    }

    public function portal(mixed $connection, UserInterface $user, string $returnUrl, ?string $configurationId = null): ?string
    {
        return 'https://paypal.com';
    }
}
