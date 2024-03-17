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

namespace Fusio\Impl\Authorization\GrantType;

use Fusio\Impl\Authorization\TokenNameBuilder;
use Fusio\Impl\Service;
use PSX\Framework\OAuth2\Credentials;
use PSX\Framework\OAuth2\GrantType\RefreshTokenAbstract;
use PSX\OAuth2\AccessToken;
use PSX\OAuth2\Grant;

/**
 * RefreshToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RefreshToken extends RefreshTokenAbstract
{
    private Service\Token $tokenService;
    private Service\System\FrameworkConfig $frameworkConfig;

    public function __construct(Service\Token $tokenService, Service\System\FrameworkConfig $frameworkConfig)
    {
        $this->tokenService = $tokenService;
        $this->frameworkConfig = $frameworkConfig;
    }

    protected function generate(Credentials $credentials, Grant\RefreshToken $grant): AccessToken
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $name = 'OAuth2 Refresh Token by ' . $userAgent . ' (' . $ip . ')';

        return $this->tokenService->refresh(
            $this->frameworkConfig->getTenantId(),
            $name,
            $grant->getRefreshToken(),
            $ip,
            $this->frameworkConfig->getExpireTokenInterval(),
            $this->frameworkConfig->getExpireRefreshInterval()
        );
    }
}
