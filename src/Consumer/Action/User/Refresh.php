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

namespace Fusio\Impl\Consumer\Action\User;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\User\Login as UserLogin;
use Fusio\Model\Consumer\UserRefresh;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;

/**
 * Refresh
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Refresh implements ActionInterface
{
    private UserLogin $loginService;

    public function __construct(UserLogin $loginService)
    {
        $this->loginService = $loginService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof UserRefresh);

        $token = $this->loginService->refresh($body);

        return $this->renderToken($token);
    }

    private function renderToken(?AccessToken $token)
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
