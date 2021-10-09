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
use PSX\Framework\Oauth2\GrantType\AuthorizationCodeAbstract;
use PSX\Oauth2\Authorization\Exception\InvalidClientException;
use PSX\Oauth2\Authorization\Exception\InvalidGrantException;
use PSX\Oauth2\Authorization\Exception\InvalidScopeException;

/**
 * AuthorizationCode
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class AuthorizationCode extends AuthorizationCodeAbstract
{
    /**
     * @var \Fusio\Impl\Service\App\Code
     */
    private $appCodeService;

    /**
     * @var \Fusio\Impl\Service\Scope
     */
    private $scopeService;

    /**
     * @var \Fusio\Impl\Service\App\Token
     */
    private $appTokenService;

    /**
     * @var \Fusio\Impl\Table\App\Code
     */
    private $appCodeTable;

    /**
     * @var string
     */
    private $expireToken;

    /**
     * @param \Fusio\Impl\Service\App\Code $appCodeService
     * @param \Fusio\Impl\Service\App\Token $appTokenService
     * @param \Fusio\Impl\Service\Scope $scopeService
     * @param \Fusio\Impl\Table\App\Code $appCodeTable
     * @param string $expireToken
     */
    public function __construct(Service\App\Code $appCodeService, Service\App\Token $appTokenService, Service\Scope $scopeService, Table\App\Code $appCodeTable, string $expireToken)
    {
        $this->appCodeService  = $appCodeService;
        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->appCodeTable    = $appCodeTable;
        $this->expireToken     = $expireToken;
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
        $code = $this->appCodeTable->getCodeByRequest(
            $credentials->getClientId(),
            $credentials->getClientSecret(),
            $code,
            $redirectUri ?: ''
        );

        if (empty($code)) {
            throw new InvalidClientException('Unknown credentials');
        }

        // check whether the code is older then 30 minutes. After that we
        // can not exchange it for an access token
        if (time() - strtotime($code['date']) > 60 * 30) {
            throw new InvalidGrantException('Code is expired');
        }

        // scopes
        $scopes = $this->scopeService->getValidScopes($code['scope'], (int) $code['app_id'], (int) $code['user_id']);
        if (empty($scopes)) {
            throw new InvalidScopeException('No valid scope given');
        }

        // generate access token
        return $this->appTokenService->generateAccessToken(
            $code['app_id'],
            $code['user_id'],
            $scopes,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            new \DateInterval($this->expireToken)
        );
    }
}
