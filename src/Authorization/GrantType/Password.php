<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Table;
use PSX\Framework\OAuth2\Credentials;
use PSX\Framework\OAuth2\GrantType\PasswordAbstract;
use PSX\OAuth2\AccessToken;
use PSX\OAuth2\Exception\InvalidClientException;
use PSX\OAuth2\Exception\InvalidGrantException;
use PSX\OAuth2\Exception\InvalidScopeException;
use PSX\OAuth2\Grant;

/**
 * Password
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Password extends PasswordAbstract
{
    private Service\User\Authenticator $authenticatorService;
    private Service\Token $tokenService;
    private Service\Scope $scopeService;
    private Service\System\FrameworkConfig $frameworkConfig;
    private Table\App $appTable;

    public function __construct(Service\User\Authenticator $authenticatorService, Service\Token $tokenService, Service\Scope $scopeService, Service\System\FrameworkConfig $frameworkConfig, Table\App $appTable)
    {
        $this->authenticatorService = $authenticatorService;
        $this->tokenService = $tokenService;
        $this->scopeService = $scopeService;
        $this->frameworkConfig = $frameworkConfig;
        $this->appTable = $appTable;
    }

    protected function generate(Credentials $credentials, Grant\Password $grant): AccessToken
    {
        $app = $this->appTable->findOneByAppKeyAndSecret($this->frameworkConfig->getTenantId(), $credentials->getClientId(), $credentials->getClientSecret());
        if (empty($app)) {
            throw new InvalidClientException('Unknown credentials');
        }

        // check user
        $userId = $this->authenticatorService->authenticate($grant->getUsername(), $grant->getPassword());
        if (empty($userId)) {
            throw new InvalidGrantException('Unknown user');
        }

        $scope = $grant->getScope();
        if (empty($scope)) {
            // as fallback simply use all scopes assigned to the user
            $scope = implode(',', $this->authenticatorService->getAvailableScopes($this->frameworkConfig->getTenantId(), $userId));
        }

        // validate scopes
        $scopes = $this->scopeService->getValidScopes($this->frameworkConfig->getTenantId(), $scope, $app->getId(), $userId);
        if (empty($scopes)) {
            throw new InvalidScopeException('No valid scope given');
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $name = $userAgent;

        // generate access token
        return $this->tokenService->generate(
            $this->frameworkConfig->getTenantId(),
            Table\Category::TYPE_AUTHORIZATION,
            $app->getId(),
            $userId,
            $name,
            $scopes,
            $ip,
            $this->frameworkConfig->getExpireTokenInterval()
        );
    }
}
