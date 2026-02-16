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

namespace Fusio\Impl\MessengerHandler;

use Fusio\Engine\ConnectorInterface;
use Fusio\Impl\Messenger\AgentSchemaTask;
use Fusio\Impl\Service\Agent\Intent;
use Fusio\Impl\Service\Agent\Sender;
use Fusio\Impl\Table;
use Fusio\Model\Backend\AgentMessageObject;
use Fusio\Model\Backend\AgentMessageText;
use Fusio\Model\Backend\AgentRequest;
use PSX\Http\Exception\BadRequestException;
use PSX\Json\Parser;
use stdClass;
use Symfony\AI\Agent\AgentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * AgentSchemaTaskHandler
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
#[AsMessageHandler]
readonly class AgentSchemaTaskHandler
{
    public function __construct(private Sender $sender, private ConnectorInterface $connector, private Table\Agent $agentTable)
    {
    }

    public function __invoke(AgentSchemaTask $action): void
    {
        $row = $action->getRow();
        $connection = $this->getConnection($row->getConnectionId());

        $input = new AgentMessageText();
        $input->setType('text');
        $input->setContent($action->getMessage());

        $request = new AgentRequest();
        $request->setIntent(Intent::SCHEMA->value);
        $request->setInput($input);

        $response = $this->sender->send(
            $connection,
            $row->getUserId(),
            $row->getConnectionId(),
            $request,
        );

        $output = $response->getOutput();

        if (!$output instanceof AgentMessageObject) {
            return;
        }

        $row->setMessage($this->appendSchemaToMessage($row->getMessage(), $action->getIndex(), $action->getType(), $output->getPayload()));

        $this->agentTable->update($row);
    }

    private function appendSchemaToMessage(string $rawMessage, int $operationIndex, string $type, mixed $schema): string
    {
        $message = Parser::decode($rawMessage);
        if (!$message instanceof stdClass) {
            return $rawMessage;
        }

        if (!isset($message->type) || $message->type !== 'object') {
            return $rawMessage;
        }

        if (!isset($message->payload) || !$message->payload instanceof stdClass) {
            return $rawMessage;
        }

        if (!isset($message->payload->schemas) || !is_array($message->payload->schemas)) {
            $message->payload->schemas = [];
        }

        $message->payload->schemas[] = [
            'index' => $operationIndex,
            'type' => $type,
            'schema' => $schema,
        ];

        return Parser::encode($message);
    }

    private function getConnection(int $connectionId): AgentInterface
    {
        $connection = $this->connector->getConnection($connectionId);
        if (!$connection instanceof AgentInterface) {
            throw new BadRequestException('Provided an invalid connection');
        }

        return $connection;
    }
}
