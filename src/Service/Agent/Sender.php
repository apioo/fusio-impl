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

use Fusio\Impl\Table;
use Fusio\Model\Backend\AgentMessage;
use Fusio\Model\Backend\AgentMessageBinary;
use Fusio\Model\Backend\AgentMessageText;
use Fusio\Model\Backend\AgentMessageToolCall;
use Fusio\Model\Backend\AgentRequest;
use Fusio\Model\Backend\AgentResponse;
use PSX\Http\Exception\BadRequestException;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Http\Exception\StatusCodeException;
use PSX\Json\Parser;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Content\File;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ToolCall;
use Throwable;

/**
 * Sender
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Sender
{
    public function __construct(private Table\Agent $agentTable, private IntentFactory $intentFactory, private ResultSerializer $resultSerializer, private MessageSerializer $messageSerializer)
    {
    }

    public function send(AgentInterface $agent, int $userId, int $connectionId, AgentRequest $request): AgentResponse
    {
        $this->agentTable->beginTransaction();

        try {
            $messages = new MessageBag();
            $messages->add(Message::forSystem($this->getIntroduction()));

            $intent = $this->intentFactory->factory($request->getIntent());

            $intentMessage = $intent->getMessage();
            if (!empty($intentMessage)) {
                $messages->add(Message::forSystem($intentMessage));
            }

            $this->loadPreviousMessages($userId, $connectionId, $messages);

            $userMessages = $this->buildUserInput($request->getInput());

            $this->persistUserMessages($userId, $connectionId, $userMessages);

            $messages->merge($userMessages);

            $options = [
                'tools' => $intent->getTools(),
            ];

            $responseFormat = $intent->getResponseFormat();
            if ($responseFormat !== null) {
                $options['response_format'] = $responseFormat;
            }

            $result = $agent->call($messages, $options);

            $output = $this->resultSerializer->serialize($result);

            $this->agentTable->addAssistantMessage($userId, $connectionId, $output);

            $this->agentTable->commit();

            $response = new AgentResponse();
            $response->setOutput($output);
            return $response;
        } catch (Throwable $e) {
            $this->agentTable->rollBack();

            if ($e instanceof StatusCodeException) {
                throw $e;
            } else {
                throw new InternalServerErrorException($e->getMessage(), $e);
            }
        }
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

    private function loadPreviousMessages(int $userId, int $connectionId, MessageBag $messages): void
    {
        $result = $this->agentTable->findMessages($userId, $connectionId);
        foreach ($result as $chat) {
            if ($chat->getType() === Table\Agent::TYPE_USER) {
                $messages->add(Message::ofUser($chat->getMessage()));
            } elseif ($chat->getType() === Table\Agent::TYPE_ASSISTANT) {
                $messages->add(Message::ofAssistant($chat->getMessage()));
            } elseif ($chat->getType() === Table\Agent::TYPE_SYSTEM) {
                $messages->add(Message::forSystem($chat->getMessage()));
            }
        }
    }

    private function buildUserInput(AgentMessage $input): MessageBag
    {
        $messages = new MessageBag();
        if ($input instanceof AgentMessageText) {
            $messages->add(Message::ofUser($input->getContent()));
        } elseif ($input instanceof AgentMessageBinary) {
            $messages->add(Message::ofUser(new File($input->getData(), $input->getMime())));
        } elseif ($input instanceof AgentMessageToolCall) {
            $functions = $input->getFunctions();
            foreach ($functions as $function) {
                $toolCall = new ToolCall($function->getId(), $function->getName(), Parser::decode($function->getArguments()));

                $messages->add(Message::ofToolCall($toolCall, ''));
            }
        } else {
            throw new BadRequestException('Provided a not supported input type');
        }

        return $messages;
    }

    private function persistUserMessages(int $userId, int $connectionId, MessageBag $userMessages): void
    {
        foreach ($userMessages as $userMessage) {
            foreach ($this->messageSerializer->serialize($userMessage) as $item) {
                $this->agentTable->addUserMessage($userId, $connectionId, $item);
            }
        }
    }
}
