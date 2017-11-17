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
use PSX\Framework\Oauth2\Credentials;
use PSX\Framework\Oauth2\GrantType\AuthorizationCodeAbstract;
use PSX\Oauth2\Authorization\Exception\InvalidClientException;
use PSX\Oauth2\Authorization\Exception\InvalidGrantException;
use PSX\Oauth2\Authorization\Exception\InvalidScopeException;

/**
 * AuthorizationCode
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AuthorizationCode extends AuthorizationCodeAbstract
{
    /**
     * @var \Fusio\Impl\Service\App\Code
     */
    protected $appCodeService;

    /**
     * @var \Fusio\Impl\Service\Scope
     */
    protected $scopeService;

    /**
     * @var \Fusio\Impl\Service\App
     */
    protected $appService;

    /**
     * @var string
     */
    protected $expireApp;

    /**
     * @param \Fusio\Impl\Service\App\Code $appCodeService
     * @param \Fusio\Impl\Service\Scope $scopeService
     * @param \Fusio\Impl\Service\App $appService
     * @param string $expireApp
     */
    public function __construct(Service\App\Code $appCodeService, Service\Scope $scopeService, Service\App $appService, $expireApp)
    {
        $this->appCodeService = $appCodeService;
        $this->scopeService   = $scopeService;
        $this->appService     = $appService;
        $this->expireApp      = $expireApp;
    }

    /**
     * @param \PSX\Framework\Oauth2\Credentials $credentials
     * @param string $code
     * @param string $redirectUri
     * @param string $clientId
     * @return \PSX\Oauth2\AccessToken
     */
    protected function generate(Credentials $credentials, $code, $redirectUri, $clientId)
    {
        $code = $this->appCodeService->getCode(
            $credentials->getClientId(),
            $credentials->getClientSecret(),
            $code,
            $redirectUri ?: ''
        );

        if (!empty($code)) {
            // check whether the code is older then 30 minutes. After that we
            // can not exchange it for an access token
            if (time() - strtotime($code['date']) > 60 * 30) {
                throw new InvalidGrantException('Code is expired');
            }

            // scopes
            $scopes = $this->scopeService->getValidScopes($code['appId'], $code['userId'], $code['scope'], ['backend']);
            if (empty($scopes)) {
                throw new InvalidScopeException('No valid scope given');
            }

            // generate access token
            return $this->appService->generateAccessToken(
                $code['appId'],
                $code['userId'],
                $scopes,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                new \DateInterval($this->expireApp)
            );
        } else {
            throw new InvalidClientException('Unknown credentials');
        }
    }
}
