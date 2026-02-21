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

namespace Fusio\Impl\Table\Agent;

use Fusio\Impl\Table\Generated;
use Fusio\Model\Backend\AgentContent;
use PSX\DateTime\LocalDateTime;
use PSX\Json\Parser;
use PSX\Sql\Condition;

/**
 * Message
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Message extends Generated\AgentMessageTable
{
    public const ORIGIN_USER      = 0x1;
    public const ORIGIN_ASSISTANT = 0x2;
    public const ORIGIN_SYSTEM    = 0x3;

    public function addUserMessage(int $agentId, int $userId, AgentContent $content): Generated\AgentMessageRow
    {
        return $this->addMessage($agentId, $userId, self::ORIGIN_USER, $content);
    }

    public function addAssistantMessage(int $agentId, int $userId, AgentContent $content): Generated\AgentMessageRow
    {
        return $this->addMessage($agentId, $userId, self::ORIGIN_ASSISTANT, $content);
    }

    public function addSystemMessage(int $agentId, int $userId, AgentContent $content): Generated\AgentMessageRow
    {
        return $this->addMessage($agentId, $userId, self::ORIGIN_SYSTEM, $content);
    }

    public function reset(int $agentId, int $userId): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Generated\AgentMessageColumn::AGENT_ID, $agentId);
        $condition->equals(Generated\AgentMessageColumn::USER_ID, $userId);

        $this->deleteBy($condition);
    }

    private function addMessage(int $agentId, int $userId, int $role, AgentContent $content): Generated\AgentMessageRow
    {
        $row = new Generated\AgentMessageRow();
        $row->setAgentId($agentId);
        $row->setUserId($userId);
        $row->setOrigin($role);
        $row->setContent(Parser::encode($content));
        $row->setInsertDate(LocalDateTime::now());
        $this->create($row);

        $row->setId($this->getLastInsertId());

        return $row;
    }
}
