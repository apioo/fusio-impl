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

namespace Fusio\Impl\Consumer\Action\User;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\User\Provider as UserProvider;
use Fusio\Model\Consumer\User_Provider;
use PSX\Http\Exception as StatusCode;
use PSX\Oauth2\AccessToken;

/**
 * Provider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Provider extends ActionAbstract
{
    private UserProvider $providerService;

    public function __construct(UserProvider $providerService)
    {
        $this->providerService = $providerService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof User_Provider);

        $token = $this->providerService->provider($request->get('provider'), $body);

        return $this->renderToken($token);
    }

    private function renderToken(?AccessToken $token): array
    {
        if ($token instanceof AccessToken) {
            return [
                'token' => $token->getAccessToken(),
                'expires_in' => $token->getExpiresIn(),
                'refresh_token' => $token->getRefreshToken(),
                'scope' => $token->getScope(),
            ];
        } else {
            throw new StatusCode\BadRequestException('Invalid name or password');
        }
    }
}
