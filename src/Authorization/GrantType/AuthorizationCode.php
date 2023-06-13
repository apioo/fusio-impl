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
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

        // check whether the code is older then 30 minutes. After that we can not exchange it for an access token
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
