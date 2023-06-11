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
use PSX\Framework\OAuth2\GrantType\PasswordAbstract;
use PSX\OAuth2\AccessToken;
use PSX\OAuth2\Exception\InvalidClientException;
use PSX\OAuth2\Exception\InvalidGrantException;
use PSX\OAuth2\Exception\InvalidScopeException;
use PSX\OAuth2\Grant;
use PSX\Sql\Condition;

/**
 * Password
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Password extends PasswordAbstract
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

    protected function generate(Credentials $credentials, Grant\Password $grant): AccessToken
    {
        $condition = Condition::withAnd();
        $condition->equals('app_key', $credentials->getClientId());
        $condition->equals('app_secret', $credentials->getClientSecret());
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->findOneBy($condition);
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
            $scope = implode(',', $this->authenticatorService->getAvailableScopes($userId));
        }

        // validate scopes
        $scopes = $this->scopeService->getValidScopes($scope, $app->getId(), $userId);
        if (empty($scopes)) {
            throw new InvalidScopeException('No valid scope given');
        }

        // generate access token
        return $this->appTokenService->generateAccessToken(
            $app->getId(),
            $userId,
            $scopes,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            new \DateInterval($this->expireToken)
        );
    }
}
