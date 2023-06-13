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

namespace Fusio\Impl\Service\User;

use Fusio\Engine\User\ProviderInterface;
use Fusio\Engine\User\UserDetails;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\UserProvider;
use Fusio\Impl\Service;
use Fusio\Model\Backend\UserRemote;
use Fusio\Model\Consumer;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;

/**
 * Provider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Provider
{
    private Service\User $userService;
    private Service\App\Token $appTokenService;
    private UserProvider $userProvider;
    private ConfigInterface $config;

    public function __construct(Service\User $userService, Service\App\Token $appTokenService, UserProvider $userProvider, ConfigInterface $config)
    {
        $this->userService = $userService;
        $this->appTokenService = $appTokenService;
        $this->userProvider = $userProvider;
        $this->config = $config;
    }

    public function provider(string $providerName, Consumer\UserProvider $request): AccessToken
    {
        $code = $request->getCode();
        if (empty($code)) {
            throw new StatusCode\BadRequestException('No code provided');
        }

        $clientId = $request->getClientId();
        if (empty($clientId)) {
            throw new StatusCode\BadRequestException('No client id provided');
        }

        $redirectUri = $request->getRedirectUri();
        if (empty($redirectUri)) {
            throw new StatusCode\BadRequestException('No redirect uri provided');
        }

        /** @var ProviderInterface $provider */
        $provider = $this->userProvider->getInstance($providerName);
        $user = $provider->requestUser($code, $clientId, $redirectUri);

        if (!$user instanceof UserDetails) {
            throw new StatusCode\BadRequestException('Could not request user information');
        }

        $remote = new UserRemote();
        $remote->setProvider($provider->getId());
        $remote->setRemoteId($user->getId());
        $remote->setName($user->getUserName());
        $remote->setEmail($user->getEmail());

        $userId = $this->userService->createRemote($remote, UserContext::newAnonymousContext());

        // get scopes for user
        $scopes = $this->userService->getAvailableScopes($userId);

        // @TODO this is the consumer app. Probably we need a better way to
        // define this id
        $appId = 2;

        return $this->appTokenService->generateAccessToken(
            $appId,
            $userId,
            $scopes,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->config->get('fusio_expire_token'))
        );
    }
}
