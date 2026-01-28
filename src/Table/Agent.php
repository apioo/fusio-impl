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

use Fusio\Impl\Service\Agent\Intent;
use Fusio\Model\Backend\AgentMessage;
use PSX\DateTime\LocalDateTime;
use PSX\Json\Parser;
use PSX\Sql\Condition;

/**
 * Agent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Agent extends Generated\AgentTable
{
    public const ORIGIN_USER      = 0x1;
    public const ORIGIN_ASSISTANT = 0x2;
    public const ORIGIN_SYSTEM    = 0x3;

    public function addUserMessage(int $userId, int $connectionId, ?Intent $intent, AgentMessage $message): void
    {
        $this->addMessage($userId, $connectionId, self::ORIGIN_USER, $intent, $message);
    }

    public function addAssistantMessage(int $userId, int $connectionId, ?Intent $intent, AgentMessage $message): void
    {
        $this->addMessage($userId, $connectionId, self::ORIGIN_ASSISTANT, $intent, $message);
    }

    public function addSystemMessage(int $userId, int $connectionId, ?Intent $intent, AgentMessage $message): void
    {
        $this->addMessage($userId, $connectionId, self::ORIGIN_SYSTEM, $intent, $message);
    }

    public function reset(int $userId, int $connectionId): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Generated\AgentColumn::USER_ID, $userId);
        $condition->equals(Generated\AgentColumn::CONNECTION_ID, $connectionId);

        $this->deleteBy($condition);
    }

    private function addMessage(int $userId, int $connectionId, int $origin, ?Intent $intent, AgentMessage $message): void
    {
        $row = new Generated\AgentRow();
        $row->setUserId($userId);
        $row->setConnectionId($connectionId);
        $row->setOrigin($origin);
        $row->setIntent($intent?->getInt() ?? 0);
        $row->setMessage(Parser::encode($message));
        $row->setInsertDate(LocalDateTime::now());
        $this->create($row);
    }
}
