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

use Fusio\Impl\Service;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * AgentCard
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class AgentCard
{
    public function __construct(
        private Service\Config $configService,
        private Table\Agent $agentTable,
        private Table\Scope $scopeTable,
        private FrameworkConfig $frameworkConfig
    ) {
    }

    public function get(): array
    {
        $scopes = $this->getScopes();

        return [
            'name' => $this->configService->getValue('info_title') ?: 'Fusio',
            'description' => $this->configService->getValue('info_description') ?: null,
            'url' => $this->frameworkConfig->getDispatchUrl('a2a', 'v1'),
            'skills' => $this->getSkills(),
            'auth' => [
                'type' => 'oauth2',
                'description' => 'Fusio OAuth2 Service used to authorize AI agents',
                'flows' => [
                    'authorizationCode' => [
                        'authorizationUrl' => $this->frameworkConfig->getDispatchUrl('authorization', 'authorize'),
                        'tokenUrl' => $this->frameworkConfig->getDispatchUrl('authorization', 'token'),
                        'scopes' => $scopes,
                    ],
                    'clientCredentials' => [
                        'tokenUrl' => $this->frameworkConfig->getDispatchUrl('authorization', 'token'),
                        'scopes' => $scopes,
                    ],
                ],
            ],
        ];
    }

    private function getSkills(): array
    {
        $skills = [];

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());

        $result = $this->agentTable->findAll($condition);
        foreach ($result as $agent) {
            $skills[] = [
                'id' => $agent->getName(),
                'name' => $agent->getName(),
                'description' => $agent->getDescription(),
            ];
        }

        return $skills;
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
