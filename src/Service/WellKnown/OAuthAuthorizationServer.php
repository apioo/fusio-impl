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

namespace Fusio\Impl\Service\WellKnown;

use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * OAuthAuthorizationServer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class OAuthAuthorizationServer
{
    public function __construct(private FrameworkConfig $frameworkConfig, private Table\Scope $scopeTable)
    {
    }

    public function get(): array
    {
        return [
            'issuer' => $this->frameworkConfig->getUrl(),
            'authorization_endpoint' => $this->frameworkConfig->getDispatchUrl('authorization', 'authorize'),
            'token_endpoint' => $this->frameworkConfig->getDispatchUrl('authorization', 'token'),
            'token_endpoint_auth_methods_supported' => ['client_secret_basic'],
            'token_endpoint_auth_signing_alg_values_supported' => [JsonWebToken::ALG],
            'userinfo_endpoint' => $this->frameworkConfig->getDispatchUrl('authorization', 'whoami'),
            'scopes_supported' => $this->getScopes(),
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'client_credentials', 'password', 'refresh_token'],
            'service_documentation' => 'https://docs.fusio-project.org/',
        ];
    }

    private function getScopes(): array
    {
        $condition = Condition::withAnd();
        $condition->equals('category_id', 1);
        $categories = $this->scopeTable->findAll($condition, 0, 1024, Table\Generated\ScopeColumn::NAME, OrderBy::ASC);

        $result = [];
        foreach ($categories as $row) {
            $result[] = $row->getName();
        }

        return $result;
    }
}
