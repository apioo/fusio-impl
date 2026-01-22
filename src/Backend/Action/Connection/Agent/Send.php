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

namespace Fusio\Impl\Backend\Action\Connection\Agent;

use Fusio\Engine\Connector;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Agent\ResultSerializer;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Model\Backend\AgentMessageText;
use Fusio\Model\Backend\AgentRequest;
use Fusio\Model\Backend\AgentResponse;
use PSX\Http\Environment\HttpResponse;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

/**
 * Send
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Send extends AgentAbstract
{
    public function __construct(private ResultSerializer $resultSerializer, Connector $connector, FrameworkConfig $frameworkConfig)
    {
        parent::__construct($connector, $frameworkConfig);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $agent = $this->getConnection($request);
        $payload = $request->getPayload();

        assert($payload instanceof AgentRequest);

        $messages = new MessageBag();
        $messages->add(Message::forSystem($this->getIntroduction()));

        $responseFormat = null;
        if ($payload->getIntent() === 'action') {
            $messages->add(Message::ofAssistant($this->getActionHint()));

            $tools = $this->getActionTools();
        } elseif ($payload->getIntent() === 'schema') {
            $messages->add(Message::ofAssistant($this->getSchemaHint()));

            $tools = $this->getSchemaTools();
            $responseFormat = $this->getSchemaResponseFormat();
        } else {
            $tools = $this->getGeneralTools();
        }

        $input = $payload->getInput();
        if ($input instanceof AgentMessageText) {
            $messages->add(Message::ofUser($input->getContent()));
        }

        $options = [
            'tools' => $tools,
        ];

        if ($responseFormat !== null) {
            $options['response_format'] = $responseFormat;
        }

        $result = $agent->call($messages, $options);

        $response = new AgentResponse();
        $response->setOutput($this->resultSerializer->serialize($result));

        return new HttpResponse(200, [], $response);
    }

    private function getIntroduction(): string
    {
        $introduction = 'You are a helpful assistant in the context of Fusio an open source API management platform and you help the user to configure the platform.' . "\n";
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
            'backend.action.getAll',
            'backend.action.get',
            'backend.action.getClasses',
            'backend.action.getForm',
            'backend.action.execute',
            'backend.action.get',
            'backend.action.update',
            'backend.action.delete',
            'backend.connection.getAll',
            'backend.connection.get',
            'backend.connection.database.getTables',
            'backend.connection.database.getTable',
            'backend.connection.database.createTable',
            'backend.connection.database.updateTable',
            'backend.connection.database.deleteTable',
            'backend.connection.database.getRows',
            'backend.connection.database.getRow',
            'backend.connection.database.createRow',
            'backend.connection.database.updateRow',
            'backend.connection.database.deleteRow',
            'backend.connection.filesystem.getAll',
            'backend.connection.filesystem.get',
            'backend.connection.filesystem.create',
            'backend.connection.filesystem.update',
            'backend.connection.filesystem.delete',
            'backend.connection.http.execute',
            'backend.connection.sdk.get',
            'backend.cronjob.getAll',
            'backend.cronjob.create',
            'backend.cronjob.get',
            'backend.cronjob.update',
            'backend.cronjob.delete',
            'backend.event.getAll',
            'backend.event.create',
            'backend.event.get',
            'backend.event.update',
            'backend.event.delete',
            'backend.log.getAll',
            'backend.log.get',
            'backend.operation.getAll',
            'backend.operation.create',
            'backend.operation.get',
            'backend.operation.update',
            'backend.operation.delete',
            'backend.schema.getAll',
            'backend.schema.create',
            'backend.schema.get',
            'backend.schema.update',
            'backend.schema.delete',
            'backend.trigger.getAll',
            'backend.trigger.create',
            'backend.trigger.get',
            'backend.trigger.update',
            'backend.trigger.delete',
        ];
    }

    private function getActionHint(): string
    {
        $hint = 'The user has the intent to develop an action. Use the "backend-action-create" tool to create a new action.' . "\n";
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
        $hint.= 'Replace the line "// [INSERT_CODE_HERE]" with the code which you have generated.';
        $hint.= '';
        $hint.= 'If the business logic wants to interact with an external service i.e. a database or remote HTTP endpoint, then you can use the getConnection method at the connector argument to access those external services.';
        $hint.= 'You can get a list of all available connections through the "backend-connection-getAll" tool.';
        $hint.= '';
        $hint.= 'If the connection has as class "Fusio.Impl.Connection.System" or "Fusio.Adapter.Sql.Connection.Sql" it is a Doctrine DBAL connection, this means you can use all methods of the Doctrine DBAL library.';
        $hint.= 'If the connection has as class "Fusio.Adapter.Http.Connection.Http" it is a Guzzle connection, this means you can use all methods of the Guzzle HTTP client library.';
        $hint.= '';
        $hint.= 'If the business logic needs to work with a database table you can get all available tables for a specific connection through the "backend-database-getTables" tool where you need to provide a connection id.';
        $hint.= 'If you need to get a concrete table schema you can use the "backend-database-getTable" tool where you need to provide the connection id and table name.';
        $hint.= 'If you need to get data from the incoming HTTP request you can get query and uri parameters through the "$request->getArguments()->get(\'[name]\')" method and the body with "$request->getPayload()".';
        $hint.= 'To add logging you can use the "$logger" argument which is a PSR-3 compatible logging interface.';
        $hint.= 'To dispatch an event you can use the "$dispatcher" argument which has a method "dispatch" where the first argument is the event name and the second the payload.';
        $hint.= '';
        $hint.= 'The generated business logic must use the build method of the "$response" factory to return a result.';
        $hint.= '';

        return $hint;
    }

    private function getActionTools(): array
    {
        return [
            'backend.action.getAll',
            'backend.action.get',
            'backend.action.getClasses',
            'backend.action.getForm',
            'backend.action.execute',
            'backend.action.get',
            'backend.action.update',
            'backend.action.delete',
            'backend.connection.getAll',
            'backend.connection.get',
            'backend.connection.database.getTables',
            'backend.connection.database.getTable',
            'backend.connection.filesystem.getAll',
            'backend.connection.filesystem.get',
            'backend.connection.http.execute',
            'backend.connection.sdk.get',
        ];
    }

    private function getSchemaHint(): string
    {
        $hint = 'The user has the intent to develop a schema. Use the "backend-schema-create" tool to create a new schema.' . "\n";
        $hint.= 'As source property of the schema you need to transform the provided user message into a TypeSchema specification.' . "\n";
        $hint.= 'The TypeSchema json structure is described through the provided JSON schema.' . "\n";
        $hint.= 'As name of the schema summarize the user message into a short and precise name as lower case and separated by hyphens.' . "\n";
        $hint.= '' . "\n";

        return $hint;
    }

    private function getSchemaTools(): array
    {
        return [
            'backend.schema.getAll',
            'backend.schema.create',
            'backend.schema.get',
            'backend.schema.update',
            'backend.schema.delete',
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
