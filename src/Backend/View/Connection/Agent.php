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

namespace Fusio\Impl\Backend\View\Connection;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
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
class Agent extends ViewAbstract
{
    /**
     * Max number of messages which are attached to the context
     */
    private const CONTEXT_MESSAGES_LENGTH = 32;

    public function getCollection(int $connectionId, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentColumn::USER_ID, $context->getUser()->getId());
        $condition->equals(Table\Generated\AgentColumn::CONNECTION_ID, $connectionId);

        $count = $this->getTable(Table\Agent::class)->getCount($condition);
        $startIndex = max(0, $count - self::CONTEXT_MESSAGES_LENGTH);
        $sortBy = Table\Generated\AgentColumn::ID;
        $sortOrder = OrderBy::ASC;

        $builder = new Builder($this->connection);

        $definition = [
            'messages' => $builder->doCollection([$this->getTable(Table\Agent::class), 'findBy'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_ID),
                'origin' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_ORIGIN),
                'message' => $builder->fieldJson(Table\Generated\AgentTable::COLUMN_MESSAGE),
                'insertDate' => $builder->fieldDateTime(Table\Generated\AgentTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }
}
