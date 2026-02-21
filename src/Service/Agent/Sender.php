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

use Fusio\Engine\ConnectorInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use Fusio\Model\Backend\AgentContent;
use Fusio\Model\Backend\AgentContentBinary;
use Fusio\Model\Backend\AgentContentObject;
use Fusio\Model\Backend\AgentContentText;
use Fusio\Model\Backend\AgentContentToolCall;
use PSX\Http\Exception\BadRequestException;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Http\Exception\NotFoundException;
use PSX\Http\Exception\StatusCodeException;
use PSX\Json\Parser;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\ObjectMapper;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaSource;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use Symfony\AI\Agent\Agent;
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
    public const CONTEXT_MESSAGES_LENGTH = 16;

    private ObjectMapper $objectMapper;

    public function __construct(
        private Table\Agent $agentTable,
        private Table\Agent\Message $messageTable,
        private Serializer\MessageSerializer $messageSerializer,
        private Serializer\ResultSerializer $resultSerializer,
        private Serializer\JsonResultSerializer $jsonResultSerializer,
        private ConnectorInterface $connector,
        private SchemaManagerInterface $schemaManager,
    ) {
        $this->objectMapper = new ObjectMapper($schemaManager);
    }

    public function send(int $agentId, int $userId, AgentContent $content, UserContext $context): AgentContent
    {
        $row = $this->agentTable->findOneByTenantAndId($context->getTenantId(), $context->getCategoryId(), $agentId);
        if (!$row instanceof Table\Generated\AgentRow) {
            throw new NotFoundException('Could not find provided agent');
        }

        $agent = $this->connector->getConnection($row->getConnectionId());
        if (!$agent instanceof Agent) {
            throw new InternalServerErrorException('Could not resolve agent connection');
        }

        $this->agentTable->beginTransaction();

        try {
            $messages = new MessageBag();
            $messages->add(Message::forSystem($row->getIntroduction()));

            $responseSchema = $this->getResponseSchema($row);
            if ($responseSchema !== null) {
                $schema = 'The output must be a valid JSON string and it must be possible to decode the output with a JSON parser.' . "\n";
                $schema.= 'The generated JSON must follow the JSON schema:' . "\n";
                $schema.= '<schema>' . "\n";
                $schema.= Parser::encode($responseSchema) . "\n";
                $schema.= '</schema>' . "\n";

                $messages->add(Message::forSystem($schema));
            }

            $messages = $this->loadPreviousMessages($agentId, $userId, $messages);

            $userMessages = $this->buildUserInput($content);

            $this->persistUserMessages($agentId, $userId, $userMessages);

            $messages = $messages->merge($userMessages);

            $options = [
                'tools' => $this->getTools($row),
                'temperature' => 0.2,
            ];

            if ($responseSchema !== null) {
                $options['response_format'] = [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => $row->getName(),
                        'strict' => true,
                        'schema' => $responseSchema,
                    ],
                ];
            }

            $result = $agent->call($messages, $options);

            if ($responseSchema !== null) {
                $output = $this->jsonResultSerializer->serialize($result);
            } else {
                $output = $this->resultSerializer->serialize($result);
            }

            $this->messageTable->addAssistantMessage($row->getId(), $userId, $output);

            $this->agentTable->commit();

            return $output;
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

    private function loadPreviousMessages(int $agentId, int $userId, MessageBag $messages): MessageBag
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentMessageColumn::AGENT_ID, $agentId);
        $condition->equals(Table\Generated\AgentMessageColumn::USER_ID, $userId);

        $count = $this->messageTable->getCount($condition);
        $startIndex = max(0, $count - self::CONTEXT_MESSAGES_LENGTH);

        $result = $this->messageTable->findBy($condition, $startIndex, $count, Table\Generated\AgentMessageColumn::ID, OrderBy::ASC);
        foreach ($result as $row) {
            $message = $this->objectMapper->readJson($row->getContent(), SchemaSource::fromClass(AgentContent::class));

            if ($row->getOrigin() === Table\Agent\Message::ORIGIN_USER) {
                $messages = $messages->merge($this->buildUserInput($message));
            } elseif ($row->getOrigin() === Table\Agent\Message::ORIGIN_ASSISTANT) {
                if ($message instanceof AgentContentText) {
                    $messages->add(Message::ofAssistant($message->getContent()));
                } elseif ($message instanceof AgentContentObject) {
                    $messages->add(Message::ofAssistant(Parser::encode($message->getPayload())));
                }
            } elseif ($row->getOrigin() === Table\Agent\Message::ORIGIN_SYSTEM) {
                if ($message instanceof AgentContentText) {
                    $messages->add(Message::forSystem($message->getContent()));
                } elseif ($message instanceof AgentContentObject) {
                    $messages->add(Message::forSystem(Parser::encode($message->getPayload())));
                }
            }
        }

        return $messages;
    }

    private function buildUserInput(AgentContent $content): MessageBag
    {
        $messages = new MessageBag();
        if ($content instanceof AgentContentText) {
            $messages->add(Message::ofUser($content->getContent()));
        } elseif ($content instanceof AgentContentObject) {
            $messages->add(Message::ofUser(Parser::encode($content->getPayload())));
        } elseif ($content instanceof AgentContentBinary) {
            $messages->add(Message::ofUser(new File($content->getData(), $content->getMime())));
        } elseif ($content instanceof AgentContentToolCall) {
            $functions = $content->getFunctions();
            foreach ($functions as $function) {
                $toolCall = new ToolCall($function->getId(), $function->getName(), Parser::decode($function->getArguments()));

                $messages->add(Message::ofToolCall($toolCall, ''));
            }
        } else {
            throw new BadRequestException('Provided a not supported message content type');
        }

        return $messages;
    }

    private function persistUserMessages(int $agentId, int $userId, MessageBag $userMessages): void
    {
        foreach ($userMessages as $userMessage) {
            foreach ($this->messageSerializer->serialize($userMessage) as $content) {
                $this->messageTable->addUserMessage($agentId, $userId, $content);
            }
        }
    }

    private function getResponseSchema(Table\Generated\AgentRow $row): ?array
    {
        $outgoing = $row->getOutgoing();
        if (empty($outgoing)) {
            return null;
        }

        $schema = $this->schemaManager->getSchema($outgoing);

        $jsonSchema = (new JsonSchema(inlineDefinitions: true))->toArray($schema->getDefinitions(), $schema->getRoot());
        if (count($jsonSchema) === 0) {
            return null;
        }

        return $jsonSchema;
    }

    private function getTools(Table\Generated\AgentRow $row): array
    {
        $rawTools = $row->getTools();
        if (empty($rawTools)) {
            return [];
        }

        $tools = Parser::decode($rawTools);
        if (!is_array($tools)) {
            return [];
        }

        return $tools;
    }
}
