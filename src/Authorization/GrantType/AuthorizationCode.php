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
use PSX\Framework\OAuth2\GrantType\AuthorizationCodeAbstract;
use PSX\OAuth2\Exception\InvalidClientException;
use PSX\OAuth2\Exception\InvalidGrantException;
use PSX\OAuth2\Exception\InvalidScopeException;
use PSX\OAuth2\Grant;

/**
 * AuthorizationCode
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class AuthorizationCode extends AuthorizationCodeAbstract
{
    private Service\App\Token $appTokenService;
    private Service\Scope $scopeService;
    private Table\App\Code $appCodeTable;
    private string $expireToken;

    public function __construct(Service\App\Token $appTokenService, Service\Scope $scopeService, Table\App\Code $appCodeTable, ConfigInterface $config)
    {
        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->appCodeTable    = $appCodeTable;
        $this->expireToken     = $config->get('fusio_expire_token');
    }

    protected function generate(Credentials $credentials, Grant\AuthorizationCode $grant)
    {
        $code = $this->appCodeTable->getCodeByRequest(
            $credentials->getClientId(),
            $credentials->getClientSecret(),
            $grant->getCode(),
            $grant->getRedirectUri()
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
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->expireToken)
        );
    }
}
