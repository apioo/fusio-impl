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

namespace Fusio\Impl\Controller\Filter;

use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service;
use PSX\Framework\Environment\IPResolver;
use PSX\Http\Exception\ClientErrorException;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

/**
 * Firewall
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Firewall implements FilterInterface
{
    public function __construct(
        private Service\Firewall $firewallService,
        private ContextFactory $contextFactory,
        private IPResolver $ipResolver,
    ) {
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        $context = $this->contextFactory->getActive();
        $ip = $this->ipResolver->resolveByRequest($request);

        $this->firewallService->assertAllowed($ip, $context->getTenantId());

        try {
            $filterChain->handle($request, $response);

            if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
                if (!$context->isCli()) {
                    $this->firewallService->handleClientErrorResponse($ip, $response->getStatusCode(), $context->getTenantId());
                }
            }
        } catch (ClientErrorException $e) {
            if (!$context->isCli()) {
                $this->firewallService->handleClientErrorResponse($ip, $e->getStatusCode(), $context->getTenantId());
            }

            throw $e;
        }
    }
}
