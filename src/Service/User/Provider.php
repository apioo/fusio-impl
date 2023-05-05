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

namespace Fusio\Impl\Service\User;

use Fusio\Engine\User\ProviderInterface;
use Fusio\Engine\User\UserDetails;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\UserProvider;
use Fusio\Impl\Service;
use Fusio\Model\Backend\UserRemote;
use Fusio\Model\Consumer;
use PSX\Framework\Config\Config;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Oauth2\AccessToken;

/**
 * Provider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
        $this->userService     = $userService;
        $this->appTokenService = $appTokenService;
        $this->userProvider    = $userProvider;
        $this->config          = $config;
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
        $user     = $provider->requestUser($code, $clientId, $redirectUri);

        if (!$user instanceof UserDetails) {
            throw new StatusCode\BadRequestException('Could not request user information');
        }

        $remote = new UserRemote();
        $remote->setProvider((string) $provider->getId());
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
