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

namespace Fusio\Impl\Service\Action;

use Fusio\Engine\Context as EngineContext;
use Fusio\Engine\Processor;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service;
use PSX\Framework\Config\Config;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Invoker
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Invoker
{
    private Processor $processor;
    private Service\Plan\Payer $planPayerService;
    private Config $config;

    public function __construct(Processor $processor, Service\Plan\Payer $planPayerService, Config $config)
    {
        $this->processor = $processor;
        $this->planPayerService = $planPayerService;
        $this->config = $config;
    }

    public function invoke(RequestInterface $request, Context $context): mixed
    {
        $method = $context->getMethod();
        $action = $method['action'];
        $costs  = (int) $method['costs'];

        $baseUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');
        $context = new EngineContext($method['route_id'], $baseUrl, $context->getApp(), $context->getUser());

        if ($costs > 0) {
            // as anonymous user it is not possible to pay
            if ($context->getUser()->isAnonymous()) {
                throw new StatusCode\ForbiddenException('This action costs points because of this you must be authenticated in order to call this action');
            }

            // in case the method has assigned costs check whether the user has
            // enough points
            $remaining = $context->getUser()->getPoints() - $costs;
            if ($remaining < 0) {
                throw new StatusCode\ClientErrorException('Your account has not enough points to call this action. Please purchase new points in order to execute this action', 429);
            }

            $this->planPayerService->pay($costs, $context);
        }

        if (!empty($action)) {
            $response = $this->processor->execute($action, $request, $context);
        } else {
            throw new StatusCode\ServiceUnavailableException('No action provided');
        }

        return $response;
    }
}
