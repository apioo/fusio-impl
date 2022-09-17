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

namespace Fusio\Impl\Consumer\Action\Payment;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Payment;
use Fusio\Model\Consumer\Payment_Portal_Request;
use PSX\Framework\Config\Config;

/**
 * Portal
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Portal extends ActionAbstract
{
    private Payment $transactionService;
    private Config $config;

    public function __construct(Payment $transactionService, Config $config)
    {
        $this->transactionService = $transactionService;
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof Payment_Portal_Request);

        $returnUrl = $body->getReturnUrl();
        if (empty($returnUrl)) {
            // in case we have no return url we use the developer portal
            $returnUrl = $this->config->get('fusio_apps_url') . '/developer';
        }

        $redirectUrl = $this->transactionService->portal(
            $request->get('provider'),
            $context->getUser(),
            $returnUrl
        );

        return [
            'redirectUrl' => $redirectUrl,
        ];
    }
}
