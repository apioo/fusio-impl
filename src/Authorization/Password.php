<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Authorization;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Oauth2\Credentials;
use PSX\Framework\Oauth2\GrantType\PasswordAbstract;
use PSX\Oauth2\Authorization\Exception\InvalidClientException;
use PSX\Oauth2\Authorization\Exception\InvalidGrantException;
use PSX\Oauth2\Authorization\Exception\InvalidScopeException;
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
    /**
     * @var \Fusio\Impl\Service\App\Token
     */
    private $appTokenService;

    /**
     * @var \Fusio\Impl\Service\Scope
     */
    private $scopeService;

    /**
     * @var \Fusio\Impl\Service\User
     */
    private $userService;

    /**
     * @var \Fusio\Impl\Table\App
     */
    private $appTable;

    /**
     * @var string
     */
    private $expireToken;

    /**
     * @param \Fusio\Impl\Service\App\Token $appTokenService
     * @param \Fusio\Impl\Service\Scope $scopeService
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Table\App $appTable
     * @param string $expireToken
     */
    public function __construct(Service\App\Token $appTokenService, Service\Scope $scopeService, Service\User $userService, Table\App $appTable, string $expireToken)
    {
        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->userService     = $userService;
        $this->appTable        = $appTable;
        $this->expireToken     = $expireToken;
    }

    /**
     * @param \PSX\Framework\Oauth2\Credentials $credentials
     * @param string $username
     * @param string $password
     * @param string $scope
     * @return \PSX\Oauth2\AccessToken
     */
    protected function generate(Credentials $credentials, $username, $password, $scope)
    {
        $condition = new Condition();
        $condition->equals('app_key', $credentials->getClientId());
        $condition->equals('app_secret', $credentials->getClientSecret());
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->getOneBy($condition);
        if (empty($app)) {
            throw new InvalidClientException('Unknown credentials');
        }

        // check user
        $userId = $this->userService->authenticateUser($username, $password);
        if (empty($userId)) {
            throw new InvalidGrantException('Unknown user');
        }

        if (empty($scope)) {
            // as fallback simply use all scopes assigned to the user
            $scope = implode(',', $this->userService->getAvailableScopes($userId));
        }

        // validate scopes
        $scopes = $this->scopeService->getValidScopes($scope, (int) $app['id'], $userId);
        if (empty($scopes)) {
            throw new InvalidScopeException('No valid scope given');
        }

        // generate access token
        return $this->appTokenService->generateAccessToken(
            $app['id'],
            $userId,
            $scopes,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            new \DateInterval($this->expireToken)
        );
    }
}
