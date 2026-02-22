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

namespace Fusio\Impl\Backend\View\Agent;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Agent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Message extends ViewAbstract
{
    public function getCollection(int $agentId, int $parentId, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentMessageColumn::AGENT_ID, $agentId);
        $condition->equals(Table\Generated\AgentMessageColumn::USER_ID, $context->getUser()->getId());

        if ($parentId > 0) {
            $condition->equals(Table\Generated\AgentMessageColumn::PARENT_ID, $parentId);
        } else {
            $condition->nil(Table\Generated\AgentMessageColumn::PARENT_ID);
        }

        $count = $this->getTable(Table\Agent\Message::class)->getCount($condition);
        $startIndex = max(0, $count - Service\Agent\Sender::CONTEXT_MESSAGES_LENGTH);
        $sortBy = Table\Generated\AgentMessageColumn::ID;
        $sortOrder = OrderBy::ASC;

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $count,
            'startIndex' => 0,
            'itemsPerPage' => Service\Agent\Sender::CONTEXT_MESSAGES_LENGTH,
            'entry' => $builder->doCollection([$this->getTable(Table\Agent\Message::class), 'findBy'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\AgentMessageTable::COLUMN_ID),
                'role' => $builder->fieldCallback(Table\Generated\AgentMessageTable::COLUMN_ORIGIN, function ($value) {
                    return match ($value) {
                        Table\Agent\Message::ORIGIN_ASSISTANT => 'assistant',
                        Table\Agent\Message::ORIGIN_SYSTEM => 'system',
                        default => 'user',
                    };
                }),
                'content' => $builder->fieldJson(Table\Generated\AgentMessageTable::COLUMN_CONTENT),
                'insertDate' => $builder->fieldDateTime(Table\Generated\AgentMessageTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }
}
