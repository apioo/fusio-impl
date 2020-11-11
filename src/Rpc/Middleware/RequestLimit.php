<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Rpc\Middleware;

use Fusio\Engine\Request\RpcRequest;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service;
use PSX\Http\Exception as StatusCode;

/**
 * RequestLimit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class RequestLimit
{
    /**
     * @var Service\Rate
     */
    private $rateService;

    /**
     * @var string
     */
    private $remoteIp;

    public function __construct(Service\Rate $rateService, string $remoteIp)
    {
        $this->rateService = $rateService;
        $this->remoteIp = $remoteIp;
    }

    public function __invoke(RpcRequest $request, Context $context)
    {
        $success = $this->rateService->assertLimit(
            $this->remoteIp,
            $context->getRouteId(),
            $context->getApp()
        );

        if (!$success) {
            throw new StatusCode\ClientErrorException('Rate limit exceeded', 429);
        }
    }
}
