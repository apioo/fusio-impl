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
use Fusio\Impl\Service\Agent\ActionSerializer;
use Fusio\Impl\Service\Agent\ResultSerializer;
use Fusio\Impl\Service\Agent\SchemaSerializer;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Model\Backend\AgentMessageText;
use Fusio\Model\Backend\AgentMessageToolCall;
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
    public function __construct(private ResultSerializer $resultSerializer, private SchemaSerializer $schemaSerializer, private ActionSerializer $actionSerializer, Connector $connector, FrameworkConfig $frameworkConfig)
    {
        parent::__construct($connector, $frameworkConfig);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $agent = $this->getConnection($request);
        $payload = $request->getPayload();

        assert($payload instanceof AgentRequest);

        $introduction = 'You are a helpful assistant in the context of Fusio an open source API management platform and you help the user to configure the platform.';
        $introduction.= 'Fusio is based on the following entities which the user can use to build powerful REST APIs:';
        $introduction.= 'Operation: An Operation defines an API endpoint, it ties together an HTTP method and a path with an underlying action.';
        $introduction.= 'Action: An Action implements the actual business logic behind an endpoint.';
        $introduction.= 'Schema: A Schema defines the structure of a JSON payload.';
        $introduction.= 'Connection: A Connection defines how to reach an external service.';
        $introduction.= 'Event: An Event is a named occurrence emitted by an action when something significant happens.';
        $introduction.= 'Cronjob: A Cronjob schedules an action to run at regular intervals.';
        $introduction.= 'Trigger: A Trigger listens for a specific Event and executes an action.';

        $messages = new MessageBag();
        $messages->add(Message::forSystem($introduction));

        $schemas = $payload->getSchemas() ?? [];
        foreach ($schemas as $schemaId) {
            $messages->add(Message::ofAssistant($this->schemaSerializer->serialize($schemaId, $context)));
        }

        $actions = $payload->getActions() ?? [];
        foreach ($actions as $actionId) {
            $messages->add(Message::ofAssistant($this->actionSerializer->serialize($actionId, $context)));
        }

        $input = $payload->getInput();
        if ($input instanceof AgentMessageText) {
            $messages->add(Message::ofUser($input->getContent()));
        } elseif ($input instanceof AgentMessageToolCall) {

        }

        $options = [
            'tools' => [
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
            ],
        ];

        $result = $agent->call($messages, $options);

        $response = new AgentResponse();
        $response->setOutput($this->resultSerializer->serialize($result));

        return new HttpResponse(200, [], $response);
    }
}
