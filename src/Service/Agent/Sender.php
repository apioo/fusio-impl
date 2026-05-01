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

use Fusio\Engine\Agent\SenderInterface;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use Fusio\Model\Agent\Input;
use Fusio\Model\Agent\Item;
use Fusio\Model\Agent\ItemObject;
use Fusio\Model\Agent\ItemText;
use Fusio\Model\Agent\Output;
use PSX\Http\Exception as StatusCode;
use PSX\Http\Exception\StatusCodeException;
use PSX\Json\Parser;
use PSX\Schema\Generator\Config;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\ObjectMapperInterface;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaSource;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Throwable;

/**
 * Sender
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Sender implements SenderInterface
{
    /**
     * Max number of messages which are attached to the context
     */
    public const CONTEXT_MESSAGES_LENGTH = 10;

    public function __construct(
        private Table\Agent $agentTable,
        private Table\Agent\Message $messageTable,
        private Serializer\MessageSerializer $messageSerializer,
        private Serializer\ResultSerializer $resultSerializer,
        private Serializer\JsonResultSerializer $jsonResultSerializer,
        private Unserializer\MessageUnserializer $messageUnserializer,
        private ConnectorInterface $connector,
        private SchemaManagerInterface $schemaManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function send(int $agentId, Input $input, ContextInterface $context): Output
    {
        $row = $this->agentTable->findOneByTenantAndId($context->getTenantId(), $context->getUser()->getCategoryId(), $agentId);
        if (!$row instanceof Table\Generated\AgentRow) {
            throw new StatusCode\NotFoundException('Could not find provided agent');
        }

        $connectionId = $row->getConnectionId();
        if (empty($connectionId)) {
            throw new StatusCode\InternalServerErrorException('No agent connection was configured, please create first an agent connection to a LLM provider like ChatGPT or Ollama in order to use an agent');
        }

        $agent = $this->connector->getConnection($connectionId);
        if (!$agent instanceof AgentInterface) {
            throw new StatusCode\InternalServerErrorException('Provided an invalid connection, the connection must be an agent connection');
        }

        $chatId = $input->getPreviousId();
        $item = $input->getItem() ?? throw new StatusCode\BadRequestException('Provided no input');

        $this->agentTable->beginTransaction();

        try {
            $messages = new MessageBag();
            $messages->add(Message::forSystem($row->getIntroduction()));

            if (!empty($chatId)) {
                $messages = $this->loadPreviousMessages($agentId, $context->getUser()->getId(), $chatId, $messages);
            }

            $userMessages = $this->messageUnserializer->unserialize($item);

            $chatId = $this->persistUserMessages($agentId, $context->getUser()->getId(), $chatId, $userMessages);

            $messages = $messages->merge($userMessages);

            $responseSchema = $this->getResponseSchema($row);

            $options = [
                'tools' => $this->getTools($row),
                'temperature' => $responseSchema !== null ? 0 : 0.4,
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
                $item = $this->jsonResultSerializer->serialize($result);
            } else {
                $item = $this->resultSerializer->serialize($result);
            }

            $this->messageTable->addAssistantMessage($row->getId(), $context->getUser()->getId(), $chatId, $item);

            $this->agentTable->commit();

            $message = new Output();
            $message->setId($chatId);
            $message->setItem($item);
            return $message;
        } catch (Throwable $e) {
            $this->agentTable->rollBack();

            if ($e instanceof StatusCodeException) {
                throw $e;
            } else {
                throw new StatusCode\InternalServerErrorException($e->getMessage(), $e);
            }
        }
    }

    private function loadPreviousMessages(int $agentId, int $userId, string $chatId, MessageBag $messages): MessageBag
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentMessageColumn::AGENT_ID, $agentId);
        $condition->equals(Table\Generated\AgentMessageColumn::USER_ID, $userId);
        $condition->equals(Table\Generated\AgentMessageColumn::CHAT_ID, $chatId);

        $count = $this->messageTable->getCount($condition);
        $startIndex = max(0, $count - self::CONTEXT_MESSAGES_LENGTH);

        $result = $this->messageTable->findBy($condition, $startIndex, $count, Table\Generated\AgentMessageColumn::ID, OrderBy::ASC);
        foreach ($result as $row) {
            $message = $this->objectMapper->readJson($row->getContent(), SchemaSource::fromClass(Item::class));

            if ($row->getOrigin() === Table\Agent\Message::ORIGIN_USER) {
                $messages = $messages->merge($this->messageUnserializer->unserialize($message));
            } elseif ($row->getOrigin() === Table\Agent\Message::ORIGIN_ASSISTANT) {
                if ($message instanceof ItemText) {
                    $messages->add(Message::ofAssistant($message->getContent()));
                } elseif ($message instanceof ItemObject) {
                    $messages->add(Message::ofAssistant(Parser::encode($message->getPayload())));
                }
            } elseif ($row->getOrigin() === Table\Agent\Message::ORIGIN_SYSTEM) {
                if ($message instanceof ItemText) {
                    $messages->add(Message::forSystem($message->getContent()));
                } elseif ($message instanceof ItemObject) {
                    $messages->add(Message::forSystem(Parser::encode($message->getPayload())));
                }
            }
        }

        return $messages;
    }

    private function persistUserMessages(int $agentId, int $userId, ?string $chatId, MessageBag $userMessages): string
    {
        foreach ($userMessages as $userMessage) {
            foreach ($this->messageSerializer->serialize($userMessage) as $content) {
                $message = $this->messageTable->addUserMessage($agentId, $userId, $chatId, $content);

                if (empty($chatId)) {
                    $chatId = $message->getChatId();
                }
            }
        }

        return $chatId;
    }

    private function getResponseSchema(Table\Generated\AgentRow $row): ?array
    {
        $outgoing = $row->getOutgoing();
        if (empty($outgoing)) {
            return null;
        }

        $schema = $this->schemaManager->getSchema($outgoing);

        $config = new Config();
        $config->put('openai_mode', true);

        $jsonSchema = (new JsonSchema($config))->toArray($schema->getDefinitions(), $schema->getRoot());
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
