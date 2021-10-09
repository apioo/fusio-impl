<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Rpc\Middleware;

use Fusio\Engine\Request\RpcRequest;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service;
use PSX\Http\Exception as StatusCode;

/**
 * Authentication
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Authentication
{
    /**
     * @var Service\Security\TokenValidator
     */
    private $tokenValidator;

    /**
     * @var string
     */
    private $authorization;

    public function __construct(Service\Security\TokenValidator $tokenValidator, ?string $authorization)
    {
        $this->tokenValidator = $tokenValidator;
        $this->authorization = $authorization;
    }

    public function __invoke(RpcRequest $request, Context $context)
    {
        $method = $context->getMethod();

        $success = $this->tokenValidator->assertAuthorization(
            $method['method'],
            $this->authorization,
            $context
        );

        if (!$success) {
            throw new StatusCode\UnauthorizedException('Could not authorize request', 'Bearer');
        }
    }
}