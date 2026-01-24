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

namespace Fusio\Impl\Table;

use Fusio\Model\Backend\AgentMessage;
use PSX\DateTime\LocalDateTime;
use PSX\Json\Parser;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Agent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Agent extends Generated\AgentTable
{
    public const TYPE_USER      = 0x1;
    public const TYPE_ASSISTANT = 0x2;
    public const TYPE_SYSTEM    = 0x3;

    /**
     * Max number of messages which are attached to the context
     */
    private const CONTEXT_MESSAGES_LENGTH = 128;

    public function findMessages(int $userId, int $connectionId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Generated\AgentColumn::USER_ID, $userId);
        $condition->equals(Generated\AgentColumn::CONNECTION_ID, $connectionId);

        $count = $this->getCount($condition);
        $startIndex = max(0, $count - self::CONTEXT_MESSAGES_LENGTH);

        return $this->findBy($condition, $startIndex, self::CONTEXT_MESSAGES_LENGTH, Generated\AgentColumn::ID, OrderBy::ASC);
    }

    public function addUserMessage(int $userId, int $connectionId, AgentMessage $message): void
    {
        $this->addMessage($userId, $connectionId, self::TYPE_USER, $message);
    }

    public function addAssistantMessage(int $userId, int $connectionId, AgentMessage $message): void
    {
        $this->addMessage($userId, $connectionId, self::TYPE_ASSISTANT, $message);
    }

    public function addSystemMessage(int $userId, int $connectionId, AgentMessage $message): void
    {
        $this->addMessage($userId, $connectionId, self::TYPE_SYSTEM, $message);
    }

    public function reset(int $userId, int $connectionId): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Generated\AgentColumn::USER_ID, $userId);
        $condition->equals(Generated\AgentColumn::CONNECTION_ID, $connectionId);

        $this->deleteBy($condition);
    }

    private function addMessage(int $userId, int $connectionId, int $type, AgentMessage $message): void
    {
        $row = new Generated\AgentRow();
        $row->setUserId($userId);
        $row->setConnectionId($connectionId);
        $row->setType($type);
        $row->setMessage(Parser::encode($message));
        $row->setInsertDate(LocalDateTime::now());
        $this->create($row);
    }
}
