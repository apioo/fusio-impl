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

namespace Fusio\Impl\Service\Agent\Intent;

use Fusio\Impl\Messenger\AgentActionTask;
use Fusio\Impl\Messenger\AgentSchemaTask;
use Fusio\Impl\Service\Agent\IntentInterface;
use Fusio\Impl\Service\Agent\Serializer\JsonResultSerializer;
use Fusio\Impl\Table\Generated\AgentRow;
use Fusio\Model\Backend\AgentMessage;
use Fusio\Model\Backend\AgentMessageObject;
use stdClass;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * ArchitectIntent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ArchitectIntent implements IntentInterface
{
    public function __construct(private JsonResultSerializer $resultSerializer, private MessageBusInterface $messageBus)
    {
    }

    public function getMessage(): string
    {
        $hint = 'The user has the intent to design new API endpoints to solve the provided task.' . "\n";
        $hint.= 'Therefor you need generate a list of operations and optional tables following the provided JSON schema.' . "\n";
        $hint.= 'You should think like an REST API expert which has deep knowledge in describing API endpoints.' . "\n";
        $hint.= 'The action of the operation should be described as text which is used later on by a different agent to generate the actual code.' . "\n";
        $hint.= 'The incoming/outgoing schemas of the operation should be described as text which is used later on by a different agent to generate the actual schema.' . "\n";
        $hint.= 'If the logic needs to persist data you also need to generate a fitting database table schemas.' . "\n";
        $hint.= 'If needed describe in the action that the user id of the current authenticated user should be used, there is no need to create a dedicated user table since it already exists in the system.' . "\n";
        $hint.= 'For primary key and user id columns it is preferred to use the type integer instead of guid.' . "\n";
        $hint.= "\n";

        return $hint;
    }

    public function getTools(): array
    {
        return [
        ];
    }

    public function getResponseSchema(): ?array
    {
        return [
            '$defs' => [
                'Parameter' => [
                    'description' => 'Object which represents a single query parameter',
                    'type' => 'object',
                    'properties' => [
                        'description' => [
                            'description' => 'A short description of this query parameter',
                            'type' => 'string',
                        ],
                        'type' => [
                            'description' => 'The data type of this query parameter',
                            'type' => 'string',
                            'enum' => ['string', 'integer', 'number', 'boolean'],
                        ],
                    ],
                    'required' => ['description', 'type'],
                    'additionalProperties' => false,
                ],
                'Parameters' => [
                    'description' => 'A map which describes all query parameters, the key is the name of the parameter and the value describes the parameter',
                    'type' => 'object',
                    'additionalProperties' => [
                        '$ref' => '#/$defs/Parameter',
                    ],
                ],
                'Operation' => [
                    'description' => 'Describes a single operation',
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'description' => 'A short and precise name as lower case which summarizes the user message use dots to group operations into logical units',
                            'type' => 'string',
                        ],
                        'active' => [
                            'description' => 'Indicates whether the operation is active',
                            'type' => 'boolean',
                        ],
                        'public' => [
                            'description' => 'Indicates whether the operation is public, if true it is possible to invoke the operation without access token',
                            'type' => 'boolean',
                        ],
                        'stability' => [
                            'description' => 'The stability of the operation, normally this should be 1 = Experimental after testing it can be set to 2 = Stable',
                            'type' => 'integer',
                        ],
                        'description' => [
                            'description' => 'A description which explains the logic of this operation. This description is also used at the automatic OpenAPI generation so it should not contain any markdown',
                            'type' => 'string',
                        ],
                        'httpMethod' => [
                            'description' => 'The HTTP method must be one of: GET, POST, PUT, PATCH, DELETE',
                            'type' => 'string',
                        ],
                        'httpPath' => [
                            'description' => 'The HTTP path must start with a "/" for variable path fragments use the notation i.e. "/endpoint/:variable_fragment"',
                            'type' => 'string',
                        ],
                        'httpCode' => [
                            'description' => 'The HTTP success status code, normally this is 200 but for create operations it should be 201',
                            'type' => 'integer',
                        ],
                        'parameters' => [
                            'description' => 'Describes all query parameters for this operation',
                            '$ref' => '#/$defs/Parameters',
                        ],
                        'incoming' => [
                            'description' => 'Describe the request schema which is required for POST and PUT methods, this text is used by a different agent to generate the actual schema so it must be precise and optimized for an LLM',
                            'type' => 'string',
                        ],
                        'outgoing' => [
                            'description' => 'Describe the response schema, this text is used by a different agent to generate the actual schema so it must be precise and optimized for an LLM',
                            'type' => 'string',
                        ],
                        'action' => [
                            'description' => 'Describes the business logic of the action, this text is used by a different agent to generate the actual action so it must be precise and optimized for an LLM',
                            'type' => 'string',
                        ],
                    ],
                    'required' => ['name', 'active', 'public', 'stability', 'description', 'httpMethod', 'httpPath', 'httpCode', 'outgoing', 'action'],
                    'additionalProperties' => false,
                ],
                'Table' => [
                    'description' => 'Represents a table on a relational database',
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'description' => 'Name of the table',
                            'type' => 'string'
                        ],
                        'columns' => [
                            'description' => 'List of table columns',
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/$defs/Column',
                            ],
                        ],
                        'primaryKey' => [
                            'description' => 'The name of the primary key column',
                            'type' => 'string'
                        ],
                    ],
                ],
                'Column' => [
                    'description' => 'Represents a column on a table',
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'description' => 'Name of the column',
                            'type' => 'string'
                        ],
                        'type' => [
                            'description' => 'Type of the column',
                            'type' => 'string',
                            'enum' => ['smallint', 'integer', 'bigint', 'string', 'text', 'guid', 'binary', 'blob', 'boolean', 'date', 'datetime', 'time', 'simple_array', 'json'],
                        ],
                        'length' => [
                            'description' => 'Optional the max column length for string types',
                            'type' => 'integer'
                        ],
                        'notNull' => [
                            'description' => 'Indicates whether null is not allowed',
                            'type' => 'boolean'
                        ],
                        'autoIncrement' => [
                            'description' => 'Indicates whether the column is autoincrement, should be only used at the primary-key column',
                            'type' => 'boolean'
                        ],
                        'precision' => [
                            'description' => 'Optional for integer types a precision',
                            'type' => 'integer'
                        ],
                        'scale' => [
                            'description' => 'Optional for integer types a scale',
                            'type' => 'integer'
                        ],
                        'default' => [
                            'description' => 'Optional the default value for the column',
                            'type' => 'string'
                        ],
                        'comment' => [
                            'description' => 'Optional a comment for the column',
                            'type' => 'string'
                        ],
                    ],
                ],
            ],
            'type' => 'object',
            'properties' => [
                'operations' => [
                    'description' => 'List of operations',
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/$defs/Operation',
                    ],
                ],
                'tables' => [
                    'description' => 'List of tables',
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/$defs/Table',
                    ],
                ],
            ],
        ];
    }

    public function transformResult(ResultInterface $result): AgentMessage
    {
        return $this->resultSerializer->serialize($result);
    }

    public function onMessagePersisted(AgentRow $row, AgentMessage $message): void
    {
        if (!$message instanceof AgentMessageObject) {
            return;
        }

        $payload = $message->getPayload();
        if (!$payload instanceof stdClass) {
            return;
        }

        $operations = $payload->operations ?? null;
        if (is_array($operations)) {
            return;
        }

        foreach ($operations as $index => $operation) {
            $incoming = $operation->incoming ?? null;
            if (!empty($incoming)) {
                $this->messageBus->dispatch(new AgentSchemaTask($row, $index, 'incoming', $incoming));
            }

            $outgoing = $operation->outgoing ?? null;
            if (!empty($outgoing)) {
                $this->messageBus->dispatch(new AgentSchemaTask($row, $index, 'outgoing', $outgoing));
            }

            $action = $operation->action ?? null;
            if (!empty($action)) {
                $this->messageBus->dispatch(new AgentActionTask($row, $index, $action));
            }
        }
    }
}
