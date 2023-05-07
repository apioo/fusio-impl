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

namespace Fusio\Impl\Controller;

use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\Log;
use Fusio\Impl\Service\Payment;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\Exception\MethodNotAllowedException;
use PSX\Http\FilterChainInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

/**
 * PaymentWebhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class PaymentWebhook extends ControllerAbstract
{
    private Payment $transactionService;
    private ResponseWriter $responseWriter;
    private Log $logService;
    private Context $context;

    public function __construct(ContextFactory $contextFactory, Payment $transactionService, ResponseWriter $responseWriter, Log $logService)
    {
        $this->transactionService = $transactionService;
        $this->responseWriter = $responseWriter;
        $this->logService = $logService;
        $this->context = $contextFactory->factory();
    }

    public function callback(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        if (!in_array($request->getMethod(), ['GET', 'POST'])) {
            throw new MethodNotAllowedException('Provided request method not allowed', ['GET', 'POST']);
        }

        $this->logService->log(
            $request->getAttribute('REMOTE_ADDR') ?: '127.0.0.1',
            $request->getMethod(),
            $request->getRequestTarget(),
            $request->getHeader('User-Agent'),
            $this->context,
            $request
        );

        try {
            $this->transactionService->webhook($this->context->getParameter('provider'), $request);
        } catch (\Throwable $e) {
            $this->logService->error($e);

            throw $e;
        } finally {
            $this->logService->finish();
        }

        $this->responseWriter->setBody($response, [
            'success' => true,
            'message' => 'Execution successful'
        ]);

        $filterChain->handle($request, $response);
    }
}
