<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Table\User;
use PSX\Framework\Oauth2\Credentials;
use PSX\Framework\Oauth2\GrantType\PasswordAbstract;
use PSX\Oauth2\Authorization\Exception\InvalidClientException;
use PSX\Oauth2\Authorization\Exception\InvalidGrantException;
use PSX\Oauth2\Authorization\Exception\InvalidRequestException;
use PSX\Oauth2\Authorization\Exception\InvalidScopeException;

/**
 * Password
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Password extends PasswordAbstract
{
    /**
     * @var \Fusio\Impl\Service\App
     */
    protected $appService;

    /**
     * @var \Fusio\Impl\Service\Scope
     */
    protected $scopeService;

    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @var string
     */
    protected $expireApp;

    /**
     * @param \Fusio\Impl\Service\App $appService
     * @param \Fusio\Impl\Service\Scope $scopeService
     * @param \Fusio\Impl\Service\User $userService
     * @param string $expireApp
     */
    public function __construct(Service\App $appService, Service\Scope $scopeService, Service\User $userService, $expireApp)
    {
        $this->appService   = $appService;
        $this->scopeService = $scopeService;
        $this->userService  = $userService;
        $this->expireApp    = $expireApp;
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
        $app = $this->appService->getByAppKeyAndSecret(
            $credentials->getClientId(),
            $credentials->getClientSecret()
        );

        if (!empty($app)) {
            // check user
            $userId = $this->userService->authenticateUser(
                $username,
                $password,
                [User::STATUS_ADMINISTRATOR, User::STATUS_CONSUMER]
            );

            // check whether user is valid
            if (empty($userId)) {
                throw new InvalidGrantException('Unknown user');
            }

            // validate scopes
            $scopes = $this->scopeService->getValidScopes($app['id'], $userId, $scope, ['backend']);
            if (empty($scopes)) {
                throw new InvalidScopeException('No valid scope given');
            }

            // generate access token
            return $this->appService->generateAccessToken(
                $app['id'],
                $userId,
                $scopes,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                new \DateInterval($this->expireApp)
            );
        } else {
            throw new InvalidClientException('Unknown credentials');
        }
    }
}
