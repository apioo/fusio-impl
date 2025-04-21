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

namespace Fusio\Impl\Controller\Filter;

use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Environment\IPResolver;
use PSX\Http\Exception\TooManyRequestsException;
use PSX\Http\Exception\UnauthorizedException;
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
        private Table\Log $logTable,
        private ContextFactory $contextFactory,
        private IPResolver $ipResolver,
    ) {
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        $context = $this->contextFactory->getActive();
        $ip = $this->ipResolver->resolveByRequest($request);

        if (!$this->firewallService->isAllowed($ip, $context)) {
            throw new TooManyRequestsException('Your IP has send to many requests please try again later', 60 * 10);
        }

        try {
            $filterChain->handle($request, $response);
        } catch (UnauthorizedException|TooManyRequestsException $e) {
            // fail2ban logic, in case a user has triggered too many 401 or 429 responses in the last 10 minutes, we insert a ban for this IP
            // for 10 minutes, this protects us from bruteforce attacks and other malicious requests

            $count = $this->logTable->getResponseCodeCount($context->getTenantId(), $ip, [401, 429], new \DateInterval('PT10M'));
            if ($count > 10) {
                $this->firewallService->createTemporaryBanForIP($context->getTenantId(), $ip, new \DateInterval('PT10M'));
            }

            throw $e;
        }
    }
}
