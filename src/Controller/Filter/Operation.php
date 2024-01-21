<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Table;
use PSX\Api\OperationInterface;
use PSX\Framework\Util\Uuid;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Operation implements FilterInterface
{
    private Table\Operation $operationTable;
    private ContextFactory $contextFactory;

    public function __construct(Table\Operation $operationTable, ContextFactory $contextFactory)
    {
        $this->operationTable = $operationTable;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        $context     = $this->contextFactory->getActive();
        $operationId = $context->getSource()[1] ?? null;
        $methodName  = $request->getMethod();

        $operation = $this->operationTable->find($operationId);

        if ($methodName === 'OPTIONS') {
            // for OPTIONS requests we only set the available request methods and directly return so the request is very
            // inexpensive and does not execute any business logic
            $availableMethods = $this->operationTable->getAvailableMethods($operation->getHttpPath());

            $globalMethods = ['OPTIONS'];
            if (in_array('GET', $availableMethods)) {
                $globalMethods[] = 'HEAD';
            }

            $response->setHeader('Allow', implode(', ', array_merge($globalMethods, $availableMethods)));
            $response->setHeader('X-Powered-By', 'Fusio');
            return;
        }

        $context->setOperation($operation);

        // add request id
        $response->setHeader('X-Request-Id', Uuid::pseudoRandom());
        $response->setHeader('X-Operation-Id', $operation->getName());
        $response->setHeader('X-Stability', match ($operation->getStability()) {
            OperationInterface::STABILITY_DEPRECATED => 'deprecated',
            OperationInterface::STABILITY_EXPERIMENTAL => 'experimental',
            OperationInterface::STABILITY_STABLE => 'stable',
            OperationInterface::STABILITY_LEGACY => 'legacy',
            default => 'unknown',
        });
        $response->setHeader('X-Powered-By', 'Fusio');

        $filterChain->handle($request, $response);
    }
}
