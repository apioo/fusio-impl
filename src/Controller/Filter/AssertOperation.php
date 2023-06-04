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
 * AssertOperation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class AssertOperation implements FilterInterface
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
