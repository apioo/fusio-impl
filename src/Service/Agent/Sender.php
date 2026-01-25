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
use PSX\Schema\ObjectMapper;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
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
    /**
     * Max number of messages which are attached to the context
     */
    public const CONTEXT_MESSAGES_LENGTH = 32;

    private ObjectMapper $objectMapper;

    public function __construct(
        private IntentFactory $intentFactory,
        private ResultSerializer $resultSerializer,
        private MessageSerializer $messageSerializer,
        private Table\Agent $agentTable,
        SchemaManager $schemaManager,
    ) {
        $this->objectMapper = new ObjectMapper($schemaManager);
    }

    public function send(AgentInterface $agent, int $userId, int $connectionId, AgentRequest $request): AgentResponse
    {
        $this->agentTable->beginTransaction();

        try {
            $messages = new MessageBag();
            $messages->add(Message::forSystem($this->getIntroduction()));

            $intent = Intent::tryFrom($request->getIntent() ?? '');
            $intentProvider = $this->intentFactory->factory($intent);

            $intentMessage = $intentProvider->getMessage();
            if (!empty($intentMessage)) {
                $messages->add(Message::forSystem($intentMessage));
            }

            $responseFormat = $intentProvider->getResponseFormat();
            if ($responseFormat !== null) {
                $schema = 'The output must be a plain JSON format and it must be possible to decode the output with a JSON processor.' . "\n";
                $schema.= 'The generated JSON format must follow the JSON schema:' . "\n";
                $schema.= '<schema>' . "\n";
                $schema.= Parser::encode($responseFormat) . "\n";
                $schema.= '</schema>' . "\n";

                $messages->add(Message::forSystem($schema));
            }

            $messages = $this->loadPreviousMessages($userId, $connectionId, $intent, $messages);

            $userMessages = $this->buildUserInput($request->getInput());

            $this->persistUserMessages($userId, $connectionId, $intent, $userMessages);

            $messages = $messages->merge($userMessages);

            $options = [
                'tools' => $intentProvider->getTools(),
            ];

            $responseFormat = $intentProvider->getResponseFormat();
            if ($responseFormat !== null) {
                $options['response_format'] = $responseFormat;
            }

            $result = $agent->call($messages, $options);

            $output = $this->resultSerializer->serialize($result);

            $this->agentTable->addAssistantMessage($userId, $connectionId, $intent, $output);

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

    private function loadPreviousMessages(int $userId, int $connectionId, ?Intent $intent, MessageBag $messages): MessageBag
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentColumn::USER_ID, $userId);
        $condition->equals(Table\Generated\AgentColumn::CONNECTION_ID, $connectionId);
        $condition->equals(Table\Generated\AgentColumn::INTENT, $intent?->getInt() ?? 0);

        $count = $this->agentTable->getCount($condition);
        $startIndex = max(0, $count - self::CONTEXT_MESSAGES_LENGTH);

        $result = $this->agentTable->findBy($condition, $startIndex, $count, Table\Generated\AgentColumn::ID, OrderBy::ASC);
        foreach ($result as $chat) {
            $message = $this->objectMapper->readJson($chat->getMessage(), SchemaSource::fromClass(AgentMessage::class));

            if ($chat->getOrigin() === Table\Agent::ORIGIN_USER) {
                $messages = $messages->merge($this->buildUserInput($message));
            } elseif ($chat->getOrigin() === Table\Agent::ORIGIN_ASSISTANT) {
                if ($message instanceof AgentMessageText) {
                    $messages->add(Message::ofAssistant($message->getContent()));
                }
            } elseif ($chat->getOrigin() === Table\Agent::ORIGIN_SYSTEM) {
                if ($message instanceof AgentMessageText) {
                    $messages->add(Message::forSystem($message->getContent()));
                }
            }
        }

        return $messages;
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

    private function persistUserMessages(int $userId, int $connectionId, ?Intent $intent, MessageBag $userMessages): void
    {
        foreach ($userMessages as $userMessage) {
            foreach ($this->messageSerializer->serialize($userMessage) as $item) {
                $this->agentTable->addUserMessage($userId, $connectionId, $intent, $item);
            }
        }
    }
}
