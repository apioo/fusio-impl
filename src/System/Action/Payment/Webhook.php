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

namespace Fusio\Impl\System\Action\Payment;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service;

/**
 * Webhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Webhook implements ActionInterface
{
    private Service\Log $logService;
    private Service\Payment $paymentService;
    private ContextFactory $contextFactory;

    public function __construct(Service\Log $logService, Service\Payment $paymentService, ContextFactory $contextFactory)
    {
        $this->logService = $logService;
        $this->paymentService = $paymentService;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $requestContext = $request->getContext();
        if (!$requestContext instanceof HttpRequestContext) {
            throw new \RuntimeException('Invoking the webhook is currently only supported through HTTP');
        }

        $httpRequest = $requestContext->getRequest();

        $this->logService->log(
            $httpRequest->getAttribute('REMOTE_ADDR') ?: '127.0.0.1',
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
            $this->logService->finish();
        }

        return [
            'success' => true,
            'message' => 'Execution successful'
        ];
    }
}
