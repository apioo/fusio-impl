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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Environment\IPResolver;
use PSX\Framework\OAuth2\Credentials;
use PSX\Framework\OAuth2\GrantType\RefreshTokenAbstract;
use PSX\Http\Exception\BadRequestException;
use PSX\OAuth2\AccessToken;
use PSX\OAuth2\Exception\ErrorExceptionAbstract;
use PSX\OAuth2\Exception\InvalidClientException;
use PSX\OAuth2\Exception\InvalidRequestException;
use PSX\OAuth2\Grant;

/**
 * RefreshToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RefreshToken extends RefreshTokenAbstract
{
    public function __construct(
        private Service\Token $tokenService,
        private Service\System\FrameworkConfig $frameworkConfig,
        private Service\Firewall $firewallService,
        private IPResolver $ipResolver,
    ) {
    }

    protected function generate(Credentials $credentials, Grant\RefreshToken $grant): AccessToken
    {
        $ip = $this->ipResolver->resolveByEnvironment();
        if (!$this->firewallService->isAllowed($ip, $this->frameworkConfig->getTenantId())) {
            throw new InvalidRequestException('Your IP has sent to many requests please try again later');
        }

        try {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
            $ip = $this->ipResolver->resolveByEnvironment();
            $name = $userAgent;

            try {
                return $this->tokenService->refresh(
                    $this->frameworkConfig->getTenantId(),
                    Table\Category::TYPE_AUTHORIZATION,
                    $name,
                    $grant->getRefreshToken(),
                    $ip,
                    $this->frameworkConfig->getExpireTokenInterval(),
                    $this->frameworkConfig->getExpireRefreshInterval()
                );
            } catch (BadRequestException $e) {
                throw new InvalidRequestException($e->getMessage());
            }
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
