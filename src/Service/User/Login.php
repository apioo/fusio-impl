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

use Fusio\Impl\Service;
use Fusio\Model\Consumer\UserLogin;
use Fusio\Model\Consumer\UserRefresh;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;

/**
 * Login
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Login
{
    private Authenticator $authenticatorService;
    private Service\App\Token $appTokenService;
    private ConfigInterface $config;

    public function __construct(Service\User\Authenticator $authenticatorService, Service\App\Token $appTokenService, ConfigInterface $config)
    {
        $this->authenticatorService = $authenticatorService;
        $this->appTokenService = $appTokenService;
        $this->config = $config;
    }

    public function login(UserLogin $login): ?AccessToken
    {
        $username = $login->getUsername();
        if (empty($username)) {
            throw new StatusCode\BadRequestException('No username provided');
        }

        $password = $login->getPassword();
        if (empty($password)) {
            throw new StatusCode\BadRequestException('No password provided');
        }

        $userId = $this->authenticatorService->authenticate($username, $password);
        if (empty($userId)) {
            return null;
        }

        $scopes = $login->getScopes();
        if (empty($scopes)) {
            $scopes = $this->authenticatorService->getAvailableScopes($userId);
        } else {
            $scopes = $this->authenticatorService->getValidScopes($userId, $scopes);
        }

        $appId = $this->getAppId();

        return $this->appTokenService->generateAccessToken(
            $appId,
            $userId,
            $scopes,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->config->get('fusio_expire_token'))
        );
    }

    public function refresh(UserRefresh $refresh): AccessToken
    {
        $refreshToken = $refresh->getRefreshToken();
        if (empty($refreshToken)) {
            throw new StatusCode\BadRequestException('No refresh token provided');
        }

        return $this->appTokenService->refreshAccessToken(
            $this->getAppId(),
            $refreshToken,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->config->get('fusio_expire_token')),
            new \DateInterval($this->config->get('fusio_expire_refresh'))
        );
    }

    private function getAppId(): int
    {
        // @TODO this is the consumer app. Probably we need a better way to
        // define this id
        $appId = 2;

        return $appId;
    }
}
