<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Environment\IPResolver;
use PSX\Framework\OAuth2\Credentials;
use PSX\Framework\OAuth2\GrantType\AuthorizationCodeAbstract;
use PSX\Http\Exception\ClientErrorException;
use PSX\OAuth2\AccessToken;
use PSX\OAuth2\Exception\ErrorExceptionAbstract;
use PSX\OAuth2\Exception\InvalidClientException;
use PSX\OAuth2\Exception\InvalidGrantException;
use PSX\OAuth2\Exception\InvalidRequestException;
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
    public function __construct(
        private Service\Token $tokenService,
        private Service\Scope $scopeService,
        private Service\System\FrameworkConfig $frameworkConfig,
        private Service\Firewall $firewallService,
        private Table\App\Code $appCodeTable,
        private IPResolver $ipResolver,
    ) {
    }

    protected function generate(Credentials $credentials, Grant\AuthorizationCode $grant): AccessToken
    {
        $ip = $this->ipResolver->resolveByEnvironment();

        try {
            $this->firewallService->assertAllowed($ip, $this->frameworkConfig->getTenantId());
        } catch (ClientErrorException $e) {
            throw new InvalidRequestException($e->getMessage(), previous: $e);
        }

        try {
            $code = $this->appCodeTable->getCodeByRequest(
                $credentials->getClientId(),
                $credentials->getClientSecret(),
                $grant->getCode(),
                $grant->getRedirectUri(),
                $this->frameworkConfig->getTenantId(),
            );

            if (empty($code)) {
                throw new InvalidClientException('Unknown credentials');
            }

            // check whether the code is older than 30 minutes. After that we can not exchange it for an access token
            $timestamp = strtotime($code['date']);
            if ($timestamp === false) {
                throw new InvalidGrantException('Provided an invalid date');
            }

            if (time() - $timestamp > 60 * 30) {
                throw new InvalidGrantException('Code is expired');
            }

            // scopes
            $scopes = $this->scopeService->getValidScopes($this->frameworkConfig->getTenantId(), $code['scope'], (int) $code['app_id'], (int) $code['user_id']);
            if (empty($scopes)) {
                throw new InvalidScopeException('No valid scope given');
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
            $name = $userAgent;

            // generate access token
            return $this->tokenService->generate(
                $this->frameworkConfig->getTenantId(),
                Table\Category::TYPE_AUTHORIZATION,
                $code['app_id'],
                $code['user_id'],
                $name,
                $scopes,
                $ip,
                $this->frameworkConfig->getExpireTokenInterval()
            );
        } catch (ClientErrorException $e) {
            $this->firewallService->handleClientErrorResponse(
                $ip,
                $e->getStatusCode(),
                $this->frameworkConfig->getTenantId()
            );

            throw new InvalidRequestException($e->getMessage(), previous: $e);
        } catch (ErrorExceptionAbstract $e) {
            $this->firewallService->handleClientErrorResponse(
                $ip,
                $e instanceof InvalidClientException ? 401 : 400,
                $this->frameworkConfig->getTenantId()
            );

            throw $e;
        }
    }
}
