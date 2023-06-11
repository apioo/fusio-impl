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

namespace Fusio\Impl\Authorization\GrantType;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\OAuth2\Credentials;
use PSX\Framework\OAuth2\GrantType\ClientCredentialsAbstract;
use PSX\OAuth2\AccessToken;
use PSX\OAuth2\Exception\InvalidClientException;
use PSX\OAuth2\Exception\InvalidScopeException;
use PSX\OAuth2\Grant;
use PSX\Sql\Condition;

/**
 * ClientCredentials
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ClientCredentials extends ClientCredentialsAbstract
{
    private Service\User\Authenticator $authenticatorService;
    private Service\App\Token $appTokenService;
    private Service\Scope $scopeService;
    private Table\App $appTable;
    private string $expireToken;

    public function __construct(Service\User\Authenticator $authenticatorService, Service\App\Token $appTokenService, Service\Scope $scopeService, Table\App $appTable, ConfigInterface $config)
    {
        $this->authenticatorService = $authenticatorService;
        $this->appTokenService = $appTokenService;
        $this->scopeService = $scopeService;
        $this->appTable = $appTable;
        $this->expireToken = $config->get('fusio_expire_token');
    }

    protected function generate(Credentials $credentials, Grant\ClientCredentials $grant): AccessToken
    {
        // check whether the credentials contain an app key and secret
        $app = $this->getApp($credentials->getClientId(), $credentials->getClientSecret());
        if (!empty($app)) {
            $appId  = $app->getId();
            $userId = $app->getUserId();
        } else {
            // otherwise try to authenticate the user credentials
            $appId  = null;
            $userId = $this->authenticatorService->authenticate($credentials->getClientId(), $credentials->getClientSecret());
        }

        if (empty($userId)) {
            throw new InvalidClientException('Unknown credentials');
        }

        $scope = $grant->getScope();
        if (empty($scope)) {
            // as fallback simply use all scopes assigned to the user
            $scope = implode(',', $this->authenticatorService->getAvailableScopes($userId));
        }

        // validate scopes
        $scopes = $this->scopeService->getValidScopes($scope, $appId, $userId);
        if (empty($scopes)) {
            throw new InvalidScopeException('No valid scope given');
        }

        // generate access token
        return $this->appTokenService->generateAccessToken(
            $appId === null ? 1 : $appId,
            $userId,
            $scopes,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->expireToken)
        );
    }

    private function getApp(string $appKey, string $appSecret): ?Table\Generated\AppRow
    {
        $condition = Condition::withAnd();
        $condition->equals('app_key', $appKey);
        $condition->equals('app_secret', $appSecret);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->findOneBy($condition);
        if (empty($app)) {
            return null;
        }

        return $app;
    }
}
