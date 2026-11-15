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

namespace Fusio\Impl\Service\Agent\A2A;

use Fusio\Impl\Service\Agent\Sender;
use Fusio\Impl\Table;
use Fusio\Model\Agent\Item;
use Fusio\Model\Agent\ItemObject;
use Fusio\Model\Agent\ItemText;
use PSX\DateTime\LocalDateTime;
use PSX\Json\Parser;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use stdClass;
use Symfony\Component\Uid\Uuid;

/**
 * Converts the Fusio agent tasks and messages into the data model of the A2A protocol
 *
 * @see https://a2a-protocol.org/latest/specification/
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Converter
{
    public const string STATE_SUBMITTED = 'TASK_STATE_SUBMITTED';
    public const string STATE_WORKING = 'TASK_STATE_WORKING';
    public const string STATE_COMPLETED = 'TASK_STATE_COMPLETED';
    public const string STATE_FAILED = 'TASK_STATE_FAILED';
    public const string STATE_CANCELED = 'TASK_STATE_CANCELED';

    public const string ROLE_USER = 'ROLE_USER';
    public const string ROLE_AGENT = 'ROLE_AGENT';

    public function __construct(private Table\Agent\Message $messageTable)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toTask(Table\Generated\AgentTaskRow $task, ?int $historyLength, bool $includeArtifacts): array
    {
        $taskId = '' . $task->getId();
        $chatId = $task->getChatId();

        $result = [
            'id' => $taskId,
        ];

        if (!empty($chatId)) {
            $result['contextId'] = $chatId;
        }

        $result['status'] = $this->toTaskStatus($task);

        if ($includeArtifacts) {
            $part = $this->itemJsonToPart($task->getItem());
            if ($part !== null) {
                $result['artifacts'] = [[
                    'artifactId' => 'agent-output',
                    'name' => 'Agent output',
                    'description' => 'The output of the agent which was processed by the task',
                    'parts' => [$part],
                ]];
            }
        }

        if (!empty($chatId) && $historyLength !== 0) {
            $result['history'] = $this->getHistory($task->getUserId(), $chatId, $historyLength ?? Sender::CONTEXT_MESSAGES_LENGTH, $taskId);
        }

        $result['metadata'] = (object) array_filter([
            'steps' => $this->getSteps($task),
        ], fn($value) => $value !== null);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function newAgentMessage(Item $item, string $contextId, ?string $taskId = null): array
    {
        $part = $this->itemToPart($item);

        $result = [
            'messageId' => Uuid::v4()->toString(),
            'contextId' => $contextId,
        ];

        if ($taskId !== null) {
            $result['taskId'] = $taskId;
        }

        $result['role'] = self::ROLE_AGENT;
        $result['parts'] = $part !== null ? [$part] : [['text' => '']];

        return $result;
    }

    public static function getState(int $status): string
    {
        return match ($status) {
            Table\Agent\Task::STATUS_RUNNING => self::STATE_WORKING,
            Table\Agent\Task::STATUS_COMPLETED => self::STATE_COMPLETED,
            Table\Agent\Task::STATUS_FAILED => self::STATE_FAILED,
            Table\Agent\Task::STATUS_CANCELED => self::STATE_CANCELED,
            default => self::STATE_SUBMITTED,
        };
    }

    /**
     * Returns the internal status for an A2A task state or null in case the state is never used by Fusio i.e.
     * TASK_STATE_INPUT_REQUIRED
     */
    public static function getStatus(string $state): ?int
    {
        return match ($state) {
            self::STATE_SUBMITTED => Table\Agent\Task::STATUS_PENDING,
            self::STATE_WORKING => Table\Agent\Task::STATUS_RUNNING,
            self::STATE_COMPLETED => Table\Agent\Task::STATUS_COMPLETED,
            self::STATE_FAILED => Table\Agent\Task::STATUS_FAILED,
            self::STATE_CANCELED => Table\Agent\Task::STATUS_CANCELED,
            default => null,
        };
    }

    public static function isValidState(string $state): bool
    {
        return in_array($state, ['TASK_STATE_UNSPECIFIED', self::STATE_SUBMITTED, self::STATE_WORKING, self::STATE_COMPLETED, self::STATE_FAILED, self::STATE_CANCELED, 'TASK_STATE_REJECTED', 'TASK_STATE_INPUT_REQUIRED', 'TASK_STATE_AUTH_REQUIRED'], true);
    }

    public static function formatDate(LocalDateTime $date): string
    {
        return $date->toDateTime()->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * @return array<string, mixed>
     */
    private function toTaskStatus(Table\Generated\AgentTaskRow $task): array
    {
        $state = self::getState($task->getStatus());

        $result = [
            'state' => $state,
        ];

        $text = $this->getStatusText($task);
        if ($text !== null) {
            $message = [
                'messageId' => 'task-' . $task->getId() . '-status',
            ];

            if (!empty($task->getChatId())) {
                $message['contextId'] = $task->getChatId();
            }

            $message['taskId'] = '' . $task->getId();
            $message['role'] = self::ROLE_AGENT;
            $message['parts'] = [['text' => $text, 'mediaType' => 'text/plain']];

            $result['message'] = $message;
        }

        $result['timestamp'] = self::formatDate($task->getEndDate() ?? $task->getStartDate() ?? $task->getInsertDate());

        return $result;
    }

    private function getStatusText(Table\Generated\AgentTaskRow $task): ?string
    {
        if (Table\Agent\Task::isFinished($task->getStatus())) {
            $message = $task->getMessage();
            if (!empty($message)) {
                return $message;
            }
        }

        // while the task is running we report the last executed step as progress
        $steps = $this->getSteps($task);
        if ($steps !== null && count($steps) > 0) {
            $last = $steps[count($steps) - 1];
            $name = $last['name'] ?? null;
            if (is_string($name)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function getSteps(Table\Generated\AgentTaskRow $task): ?array
    {
        $steps = $task->getSteps();
        if (empty($steps)) {
            return null;
        }

        return array_values(Parser::decodeAsArray($steps));
    }

    /**
     * Returns the last messages of the conversation (A2A context) in chronological order
     *
     * @return list<array<string, mixed>>
     */
    private function getHistory(int $userId, string $chatId, int $length, string $taskId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentMessageColumn::USER_ID, $userId);
        $condition->equals(Table\Generated\AgentMessageColumn::CHAT_ID, $chatId);
        $condition->in(Table\Generated\AgentMessageColumn::ORIGIN, [Table\Agent\Message::ORIGIN_USER, Table\Agent\Message::ORIGIN_ASSISTANT]);

        $rows = $this->messageTable->findBy($condition, 0, $length, Table\Generated\AgentMessageColumn::ID, OrderBy::DESC);

        $result = [];
        foreach (array_reverse($rows) as $row) {
            $part = $this->itemJsonToPart($row->getContent());
            if ($part === null) {
                // i.e. tool calls are not part of the A2A history
                continue;
            }

            $result[] = [
                'messageId' => 'message-' . $row->getId(),
                'contextId' => $chatId,
                'taskId' => $taskId,
                'role' => $row->getOrigin() === Table\Agent\Message::ORIGIN_USER ? self::ROLE_USER : self::ROLE_AGENT,
                'parts' => [$part],
            ];
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function itemJsonToPart(string $json): ?array
    {
        $data = Parser::decode($json);
        if (!$data instanceof stdClass) {
            return null;
        }

        $type = $data->type ?? null;
        if ($type === 'text' && isset($data->content) && is_string($data->content)) {
            return ['text' => $data->content, 'mediaType' => 'text/plain'];
        } elseif ($type === 'object' && property_exists($data, 'payload')) {
            return ['data' => $data->payload, 'mediaType' => 'application/json'];
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function itemToPart(Item $item): ?array
    {
        if ($item instanceof ItemText) {
            return ['text' => $item->getContent() ?? '', 'mediaType' => 'text/plain'];
        } elseif ($item instanceof ItemObject) {
            return ['data' => $item->getPayload(), 'mediaType' => 'application/json'];
        }

        return null;
    }
}
