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
use PSX\Framework\OAuth2\GrantType\RefreshTokenAbstract;
use PSX\OAuth2\AccessToken;
use PSX\OAuth2\Exception\ServerErrorException;
use PSX\OAuth2\Grant;
use PSX\Sql\Condition;

/**
 * RefreshToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RefreshToken extends RefreshTokenAbstract
{
    private Service\App\Token $appTokenService;
    private Table\App $appTable;
    private ConfigInterface $config;

    public function __construct(Service\App\Token $appTokenService, Table\App $appTable, ConfigInterface $config)
    {
        $this->appTokenService = $appTokenService;
        $this->appTable        = $appTable;
        $this->config          = $config;
    }

    protected function generate(Credentials $credentials, Grant\RefreshToken $grant): AccessToken
    {
        $app = $this->appTable->findOneByAppKeyAndSecret($this->getTenantId(), $credentials->getClientId(), $credentials->getClientSecret());
        if (empty($app)) {
            throw new ServerErrorException('Unknown credentials');
        }

        // refresh access token
        return $this->appTokenService->refreshAccessToken(
            $this->getTenantId(),
            $app->getId(),
            $grant->getRefreshToken(),
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->config->get('fusio_expire_token')),
            new \DateInterval($this->config->get('fusio_expire_refresh') ?? 'P3D')
        );
    }

    private function getTenantId(): ?string
    {
        $tenantId = $this->config->get('fusio_tenant_id');
        if (empty($tenantId)) {
            return null;
        }

        return $tenantId;
    }
}
