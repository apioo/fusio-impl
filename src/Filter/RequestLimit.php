<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Filter;

use Fusio\Engine\Repository\AppInterface;
use Fusio\Impl\Loader\Context;
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
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RequestLimit implements FilterInterface
{
    /**
     * @var \Fusio\Impl\Service\Rate
     */
    protected $rateService;

    /**
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    /**
     * @param \Fusio\Impl\Service\Rate $rateService
     * @param \Fusio\Impl\Loader\Context $context
     */
    public function __construct(Service\Rate $rateService, Context $context)
    {
        $this->rateService   = $rateService;
        $this->context       = $context;
    }

    /**
     * @inheritdoc
     */
    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain)
    {
        $success = $this->rateService->assertLimit(
            $request->getAttribute('REMOTE_ADDR') ?: '127.0.0.1',
            $this->context->getRouteId(),
            $this->context->getApp(),
            $response
        );

        if ($success) {
            $filterChain->handle($request, $response);
        } else {
            throw new StatusCode\ClientErrorException('Rate limit exceeded', 429);
        }
    }
}
