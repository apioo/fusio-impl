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

use Fusio\Impl\Base;
use Fusio\Impl\Service\Agent\A2A\Server;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
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
    /**
     * The scope which is required to access the A2A endpoint, it grants access to all backend agent operations
     */
    public const string REQUIRED_SCOPE = 'backend.agent';

    public function __construct(
        private Table\Agent $agentTable,
        private Table\Scope $scopeTable,
        private FrameworkConfig $frameworkConfig
    ) {
    }

    /**
     * Returns the agent card according to the A2A specification. We only expose the architect agent of the instance
     *
     * @see https://a2a-protocol.org/latest/specification/
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $agent = $this->findAgent();
        $scopes = $this->getScopes();
        $tokenUrl = $this->frameworkConfig->getDispatchUrl('authorization', 'token');
        $outputMode = !empty($agent->getOutgoing()) ? 'application/json' : 'text/plain';

        return [
            'name' => $agent->getName(),
            'description' => $agent->getDescription(),
            'supportedInterfaces' => [
                [
                    'url' => $this->frameworkConfig->getDispatchUrl('a2a', 'v1'),
                    'protocolBinding' => 'JSONRPC',
                    'protocolVersion' => Server::PROTOCOL_VERSION,
                ],
            ],
            'version' => Base::getVersion() ?? '0.0.0',
            'capabilities' => [
                'streaming' => false,
                'pushNotifications' => false,
                'extendedAgentCard' => false,
            ],
            'defaultInputModes' => ['text/plain', 'application/json'],
            'defaultOutputModes' => [$outputMode],
            'skills' => [
                [
                    'id' => $agent->getName(),
                    'name' => $agent->getName(),
                    'description' => $agent->getDescription(),
                    'tags' => ['fusio', self::getTypeTag($agent->getType())],
                    'inputModes' => ['text/plain', 'application/json'],
                    'outputModes' => [$outputMode],
                ],
            ],
            'securitySchemes' => [
                'oauth2' => [
                    'oauth2SecurityScheme' => [
                        'description' => 'Fusio OAuth2 authorization, the token requires the "backend.agent" scope',
                        'flows' => [
                            'authorizationCode' => [
                                'authorizationUrl' => $this->frameworkConfig->getDispatchUrl('authorization', 'authorize'),
                                'tokenUrl' => $tokenUrl,
                                'scopes' => $scopes,
                            ],
                        ],
                    ],
                ],
                'oauth2ClientCredentials' => [
                    'oauth2SecurityScheme' => [
                        'description' => 'Fusio OAuth2 client credentials, the app requires the "backend.agent" scope',
                        'flows' => [
                            'clientCredentials' => [
                                'tokenUrl' => $tokenUrl,
                                'scopes' => $scopes,
                            ],
                        ],
                    ],
                ],
            ],
            'securityRequirements' => [
                ['schemes' => ['oauth2' => ['list' => [self::REQUIRED_SCOPE]]]],
                ['schemes' => ['oauth2ClientCredentials' => ['list' => [self::REQUIRED_SCOPE]]]],
            ],
        ];
    }

    private function findAgent(): Table\Generated\AgentRow
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\AgentTable::COLUMN_TYPE, Table\Agent::TYPE_ARCHITECT);
        $condition->equals(Table\Generated\AgentTable::COLUMN_STATUS, Table\Agent::STATUS_ACTIVE);

        $agent = $this->agentTable->findAll($condition, 0, 1, Table\Generated\AgentColumn::ID, OrderBy::ASC)[0] ?? null;
        if (!$agent instanceof Table\Generated\AgentRow) {
            throw new StatusCode\NotFoundException('The architect agent is not available');
        }

        return $agent;
    }

    private static function getTypeTag(int $type): string
    {
        return match ($type) {
            Table\Agent::TYPE_ARCHITECT => 'architect',
            Table\Agent::TYPE_ACTION => 'action',
            Table\Agent::TYPE_SCHEMA => 'schema',
            Table\Agent::TYPE_DATABASE => 'database',
            Table\Agent::TYPE_SEED => 'seed',
            default => 'general',
        };
    }

    /**
     * Returns the scopes which are relevant for the A2A endpoint as map of scope name to description
     *
     * @return array<string, string>
     */
    private function getScopes(): array
    {
        $result = [];
        foreach (['backend', self::REQUIRED_SCOPE] as $name) {
            $row = $this->scopeTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), null, $name);
            if ($row instanceof Table\Generated\ScopeRow) {
                $result[$name] = $row->getDescription() ?: 'Access to the ' . $name . ' scope';
            }
        }

        return $result;
    }
}
