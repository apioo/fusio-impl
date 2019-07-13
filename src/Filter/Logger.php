<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Loader\Context;
use Fusio\Impl\Service;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

/**
 * Logger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Logger implements FilterInterface
{
    /**
     * @var \Fusio\Impl\Service\Log
     */
    protected $logService;

    /**
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    public function __construct(Service\Log $logService, Context $context)
    {
        $this->logService = $logService;
        $this->context    = $context;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain)
    {
        $this->logService->log(
            $request->getAttribute('REMOTE_ADDR') ?: '127.0.0.1',
            $request->getMethod(),
            $request->getRequestTarget(),
            $request->getHeader('User-Agent'),
            $this->context,
            $request
        );

        try {
            $filterChain->handle($request, $response);
        } catch (\Throwable $e) {
            $this->logService->error($e);

            throw $e;
        } finally {
            $this->logService->finish();
        }
    }
}
