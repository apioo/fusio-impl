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

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use Fusio\Engine\Model;
use Fusio\Engine\Repository\AppInterface;
use Fusio\Engine\Repository\UserInterface;
use Fusio\Impl\Loader\Context;
use Fusio\Impl\Service\Security\TokenValidator;
use Fusio\Impl\Table\App\Token as AppToken;
use PSX\Http\Exception\UnauthorizedException;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Oauth2\Authorization\Exception\InvalidScopeException;

/**
 * Authentication
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Authentication implements FilterInterface
{
    /**
     * @var \Fusio\Impl\Service\Security\TokenValidator
     */
    protected $tokenValidator;

    /**
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    public function __construct(TokenValidator $tokenValidator, Context $context)
    {
        $this->tokenValidator = $tokenValidator;
        $this->context        = $context;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain)
    {
        $success = $this->tokenValidator->assertAuthorization(
            $request->getMethod(),
            $request->getHeader('Authorization'),
            $this->context
        );

        if ($success) {
            $filterChain->handle($request, $response);
        } else {
            throw new UnauthorizedException('Could not authorize request', 'Bearer');
        }
    }
}
