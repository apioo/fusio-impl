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
use Fusio\Impl\Framework\Loader\ContextPropertyNotSetException;
use Fusio\Impl\Service;
use PSX\Http\Exception as StatusCode;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

/**
 * RequestLimit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RequestLimit implements FilterInterface
{
    private Service\Rate\Limiter $limiterService;
    private ContextFactory $contextFactory;

    public function __construct(Service\Rate\Limiter $limiterService, ContextFactory $contextFactory)
    {
        $this->limiterService = $limiterService;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        $context = $this->contextFactory->getActive();

        $success = $this->limiterService->assertLimit(
            $request->getAttribute('REMOTE_ADDR') ?: '127.0.0.1',
            $context->getOperation(),
            $context->getApp(),
            $context->getUser(),
            $response
        );

        if ($success) {
            $filterChain->handle($request, $response);
        } else {
            throw new StatusCode\ClientErrorException('Rate limit exceeded', 429);
        }
    }
}
