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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\UserLogin;
use Fusio\Model\Consumer\UserRefresh;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;

/**
 * Login
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Login
{
    private Authenticator $authenticatorService;
    private Service\App\Token $appTokenService;
    private Table\App $appTable;
    private ConfigInterface $config;

    public function __construct(Service\User\Authenticator $authenticatorService, Service\App\Token $appTokenService, Table\App $appTable, ConfigInterface $config)
    {
        $this->authenticatorService = $authenticatorService;
        $this->appTokenService = $appTokenService;
        $this->appTable = $appTable;
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

        $appId = $this->getAppId($login->getAppKey());

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

        $appId = $this->getAppId($refresh->getAppKey());

        return $this->appTokenService->refreshAccessToken(
            $appId,
            $refreshToken,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->config->get('fusio_expire_token')),
            new \DateInterval($this->config->get('fusio_expire_refresh'))
        );
    }

    private function getAppId(?string $appKey): int
    {
        if (empty($appKey)) {
            // in case the user has not provided an app key we simply use the consumer app
            return 2;
        }

        $existing = $this->appTable->findOneByAppKey($appKey);
        if (!$existing instanceof Table\Generated\AppRow) {
            throw new StatusCode\BadRequestException('Provided an invalid app key');
        }

        return $existing->getId();
    }
}
