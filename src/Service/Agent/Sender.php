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

namespace Fusio\Impl\Service\Agent;

use Fusio\Model\Backend\AgentMessageText;
use Fusio\Model\Backend\AgentRequest;
use Fusio\Model\Backend\AgentResponse;
use Fusio\Impl\Table;
use PSX\Http\Exception\InternalServerErrorException;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Agent\Exception\ExceptionInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\TextResult;

/**
 * Sender
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Sender
{
    public function __construct(private Table\Agent $agentChatTable, private ResultSerializer $resultSerializer)
    {
    }

    public function send(AgentInterface $agent, int $userId, int $connectionId, AgentRequest $request): AgentResponse
    {
        $messages = new MessageBag();
        $messages->add(Message::forSystem($this->getIntroduction()));

        $responseFormat = null;
        if ($request->getIntent() === 'action') {
            $messages->add(Message::forSystem($this->getActionHint()));

            $tools = $this->getActionTools();
        } elseif ($request->getIntent() === 'schema') {
            $messages->add(Message::forSystem($this->getSchemaHint()));

            $tools = $this->getSchemaTools();
            $responseFormat = $this->getSchemaResponseFormat();
        } else {
            $tools = $this->getGeneralTools();
        }

        $result = $this->agentChatTable->findMessages($userId, $connectionId);
        foreach ($result as $chat) {
            if ($chat->getType() === Table\Agent::TYPE_USER) {
                $messages->add(Message::ofUser($chat->getMessage()));
            } elseif ($chat->getType() === Table\Agent::TYPE_ASSISTANT) {
                $messages->add(Message::ofAssistant($chat->getMessage()));
            }
        }

        $input = $request->getInput();
        if ($input instanceof AgentMessageText) {
            $messages->add(Message::ofUser($input->getContent()));

            $this->agentChatTable->add(
                $userId,
                $connectionId,
                Table\Agent::TYPE_USER,
                $input->getContent()
            );
        }

        $options = [
            'tools' => $tools,
        ];

        if ($responseFormat !== null) {
            $options['response_format'] = $responseFormat;
        }

        try {
            $result = $agent->call($messages, $options);
        } catch (ExceptionInterface $e) {
            throw new InternalServerErrorException($e->getMessage(), $e);
        }

        if ($result instanceof TextResult) {
            $this->agentChatTable->add(
                $userId,
                $connectionId,
                Table\Agent::TYPE_ASSISTANT,
                $result->getContent()
            );
        }

        $response = new AgentResponse();
        $response->setOutput($this->resultSerializer->serialize($result));

        return $response;
    }

    private function getIntroduction(): string
    {
        $introduction = 'You are a helpful assistant in the context of Fusio an open source API management platform.' . "\n";
        $introduction.= 'You help the user to configure or get information about the Fusio instance.' . "\n";
        $introduction.= 'Fusio is based on the following entities which the user can use to build powerful REST APIs:' . "\n";
        $introduction.= 'Operation: An Operation defines an API endpoint, it ties together an HTTP method and a path with an underlying action.' . "\n";
        $introduction.= 'Action: An Action implements the actual business logic behind an endpoint.' . "\n";
        $introduction.= 'Schema: A Schema defines the structure of a JSON payload.' . "\n";
        $introduction.= 'Connection: A Connection defines how to reach an external service.' . "\n";
        $introduction.= 'Event: An Event is a named occurrence emitted by an action when something significant happens.' . "\n";
        $introduction.= 'Cronjob: A Cronjob schedules an action to run at regular intervals.' . "\n";
        $introduction.= 'Trigger: A Trigger listens for a specific Event and executes an action.' . "\n";

        return $introduction;
    }

    private function getGeneralTools(): array
    {
        return [
            'backend_action_getAll',
            'backend_action_get',
            'backend_action_getClasses',
            'backend_action_getForm',
            'backend_action_execute',
            'backend_action_get',
            'backend_action_update',
            'backend_action_delete',
            'backend_connection_getAll',
            'backend_connection_get',
            'backend_connection_database_getTables',
            'backend_connection_database_getTable',
            'backend_connection_database_createTable',
            'backend_connection_database_updateTable',
            'backend_connection_database_deleteTable',
            'backend_connection_database_getRows',
            'backend_connection_database_getRow',
            'backend_connection_database_createRow',
            'backend_connection_database_updateRow',
            'backend_connection_database_deleteRow',
            'backend_connection_filesystem_getAll',
            'backend_connection_filesystem_get',
            'backend_connection_filesystem_create',
            'backend_connection_filesystem_update',
            'backend_connection_filesystem_delete',
            'backend_connection_http_execute',
            'backend_connection_sdk_get',
            'backend_cronjob_getAll',
            'backend_cronjob_create',
            'backend_cronjob_get',
            'backend_cronjob_update',
            'backend_cronjob_delete',
            'backend_event_getAll',
            'backend_event_create',
            'backend_event_get',
            'backend_event_update',
            'backend_event_delete',
            'backend_log_getAll',
            'backend_log_get',
            'backend_operation_getAll',
            'backend_operation_create',
            'backend_operation_get',
            'backend_operation_update',
            'backend_operation_delete',
            'backend_schema_getAll',
            'backend_schema_create',
            'backend_schema_get',
            'backend_schema_update',
            'backend_schema_delete',
            'backend_trigger_getAll',
            'backend_trigger_create',
            'backend_trigger_get',
            'backend_trigger_update',
            'backend_trigger_delete',
        ];
    }

    private function getActionHint(): string
    {
        $hint = 'The user has the intent to develop an action. Use the "backend_action_create" tool to create a new action.' . "\n";
        $hint.= 'Use as class "Fusio.Adapter.Worker.Action.WorkerPHPLocal" and add a key "code" to the config property and for the value you need to transform the provided user message into PHP code.' . "\n";
        $hint.= "\n";
        $hint.= 'As name of the action summarize the user message into a short and precise name as lower case and separated by hyphens.' . "\n";
        $hint.= 'The code which you generate is used as an action inside Fusio, this code helps to implement custom business logic for the user.' . "\n";
        $hint.= 'The resulting PHP code must be wrapped into the following code:' . "\n";
        $hint.= "\n";
        $hint.= '--' . "\n";
        $hint.= <<<PHP
<?php

use Doctrine\DBAL\Connection;
use Fusio\Worker\ExecuteContext;
use Fusio\Worker\ExecuteRequest;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Response\FactoryInterface;
use Fusio\Engine\DispatcherInterface;
use Psr\Log\LoggerInterface;

return function(ExecuteRequest \$request, ExecuteContext \$context, ConnectorInterface \$connector, FactoryInterface \$response, DispatcherInterface \$dispatcher, LoggerInterface \$logger) {

// [INSERT_CODE_HERE]

};

PHP;
        $hint.= '--' . "\n";
        $hint.= "\n";
        $hint.= 'Replace the line "// [INSERT_CODE_HERE]" with the code which you have generated.' . "\n";
        $hint.= "\n";
        $hint.= 'If the business logic wants to interact with an external service i.e. a database or remote HTTP endpoint, then you can use the getConnection method at the connector argument to access those external services.' . "\n";
        $hint.= 'You can get a list of all available connections through the "backend_connection_getAll" tool.' . "\n";
        $hint.= "\n";
        $hint.= 'If the connection has as class "Fusio.Impl.Connection.System" or "Fusio.Adapter.Sql.Connection.Sql" it is a Doctrine DBAL connection, this means you can use all methods of the Doctrine DBAL library.' . "\n";
        $hint.= 'If the connection has as class "Fusio.Adapter.Http.Connection.Http" it is a Guzzle connection, this means you can use all methods of the Guzzle HTTP client library.' . "\n";
        $hint.= "\n";
        $hint.= 'If the business logic needs to work with a database table you can get all available tables for a specific connection through the "backend_database_getTables" tool where you need to provide a connection id.' . "\n";
        $hint.= 'If you need to get a concrete table schema you can use the "backend_database_getTable" tool where you need to provide the connection id and table name.' . "\n";
        $hint.= 'If you need to get data from the incoming HTTP request you can get query and uri parameters through the "$request->getArguments()->get(\'[name]\')" method and the body with "$request->getPayload()".' . "\n";
        $hint.= 'To add logging you can use the "$logger" argument which is a PSR-3 compatible logging interface.' . "\n";
        $hint.= 'To dispatch an event you can use the "$dispatcher" argument which has a method "dispatch" where the first argument is the event name and the second the payload.' . "\n";
        $hint.= "\n";
        $hint.= 'The generated business logic must use the build method of the "$response" factory to return a result.' . "\n";
        $hint.= "\n";
        $hint.= 'You can also use the "backend_action_execute" tool to test the action which you have created.' . "\n";

        return $hint;
    }

    private function getActionTools(): array
    {
        return [
            'backend_action_getAll',
            'backend_action_get',
            'backend_action_getClasses',
            'backend_action_getForm',
            'backend_action_execute',
            'backend_action_get',
            'backend_action_update',
            'backend_action_delete',
            'backend_connection_getAll',
            'backend_connection_get',
            'backend_connection_database_getTables',
            'backend_connection_database_getTable',
            'backend_connection_filesystem_getAll',
            'backend_connection_filesystem_get',
            'backend_connection_http_execute',
            'backend_connection_sdk_get',
        ];
    }

    private function getSchemaHint(): string
    {
        $hint = 'The user has the intent to develop a schema. Use the "backend_schema_create" tool to create a new schema.' . "\n";
        $hint.= 'As name property of the schema summarize the user message into a short and precise name as lower case and separated by hyphens.' . "\n";
        $hint.= 'As source property of the schema you need to transform the provided user message into a TypeSchema specification.' . "\n";
        $hint.= 'The TypeSchema json structure is described through the provided JSON schema.' . "\n";
        $hint.= "\n";

        return $hint;
    }

    private function getSchemaTools(): array
    {
        return [
            'backend_schema_getAll',
            'backend_schema_create',
            'backend_schema_get',
            'backend_schema_update',
            'backend_schema_delete',
        ];
    }

    private function getSchemaResponseFormat(): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'TypeSchema',
                'strict' => true,
                'schema' => [
                    '$defs' => [
                        'PropertyType' => [
                            'anyOf' => [
                                [
                                    'description' => 'Boolean type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['boolean'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Integer type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['integer'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Number type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['number'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'String type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['string'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Reference type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['reference'],
                                        ],
                                        'target' => [
                                            'description' => 'The reference target must be a key which is available under the definitions object',
                                            'type' => 'string',
                                        ],
                                    ],
                                    'required' => ['type', 'target'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'DefinitionType' => [
                            'anyOf' => [
                                [
                                    'description' => 'Struct type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['struct'],
                                        ],
                                        'properties' => [
                                            'type' => 'object',
                                            'additionalProperties' => [
                                                '$ref' => '#/$defs/PropertyType',
                                            ],
                                        ],
                                    ],
                                    'required' => ['type', 'properties'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Map type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['map'],
                                        ],
                                        'schema' => [
                                            '$ref' => '#/$defs/PropertyType',
                                        ],
                                    ],
                                    'required' => ['type', 'schema'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Array type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['array'],
                                        ],
                                        'schema' => [
                                            '$ref' => '#/$defs/PropertyType',
                                        ],
                                    ],
                                    'required' => ['type', 'schema'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                    ],
                    'type' => 'object',
                    'properties' => [
                        'definitions' => [
                            'description' => 'A map of definition types',
                            'type' => 'object',
                            'additionalProperties' => [
                                '$ref' => '#/$defs/DefinitionType',
                            ]
                        ],
                        'root' => [
                            'description' => 'A reference to the root type which must be a key at the definitions map',
                            'type' => 'string',
                        ],
                    ],
                    'required' => ['definitions', 'root'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
