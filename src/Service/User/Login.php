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
use Fusio\Model\Consumer\User_Login;
use Fusio\Model\Consumer\User_Refresh;
use PSX\Framework\Config\Config;
use PSX\Oauth2\AccessToken;

/**
 * Login
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Login
{
    private Service\User $userService;
    private Service\App\Token $appTokenService;
    private Config $config;

    public function __construct(Service\User $userService, Service\App\Token $appTokenService, Config $config)
    {
        $this->userService     = $userService;
        $this->appTokenService = $appTokenService;
        $this->config          = $config;
    }

    public function login(User_Login $login): ?AccessToken
    {
        $userId = $this->userService->authenticateUser($login->getUsername(), $login->getPassword());
        if (empty($userId)) {
            return null;
        }

        $scopes = $login->getScopes();
        if (empty($scopes)) {
            $scopes = $this->userService->getAvailableScopes($userId);
        } else {
            $scopes = $this->userService->getValidScopes($userId, $scopes);
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

    public function refresh(User_Refresh $refresh): AccessToken
    {
        return $this->appTokenService->refreshAccessToken(
            $this->getAppId(),
            $refresh->getRefresh_token(),
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
