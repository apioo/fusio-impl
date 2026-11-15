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
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Task
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Task extends ViewAbstract
{
    public function getCollection(int $agentId, QueryFilter $filter, ContextInterface $context): mixed
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AgentTaskTable::COLUMN_AGENT_ID, $agentId);
        $condition->equals(Table\Generated\AgentTaskTable::COLUMN_USER_ID, $context->getUser()->getId());

        $builder = new Builder($this->connection);

        $definition = [
            'kind' => $builder->fieldValue(Service\TypeSystem\KindBuilder::build(Model\Backend\AgentTaskCollection::class)),
            'totalResults' => $this->getTable(Table\Agent\Task::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Agent\Task::class), 'findBy'], [$condition, $startIndex, $count, Table\Generated\AgentTaskColumn::ID, OrderBy::DESC], [
                'kind' => $builder->fieldValue(Service\TypeSystem\KindBuilder::build(Model\Backend\AgentTask::class)),
                'id' => $builder->fieldInteger(Table\Generated\AgentTaskTable::COLUMN_ID),
                'status' => $builder->fieldCallback(Table\Generated\AgentTaskTable::COLUMN_STATUS, fn($value) => Table\Agent\Task::getStatusName((int) $value)),
                'message' => Table\Generated\AgentTaskTable::COLUMN_MESSAGE,
                'startDate' => $builder->fieldDateTime(Table\Generated\AgentTaskTable::COLUMN_START_DATE),
                'endDate' => $builder->fieldDateTime(Table\Generated\AgentTaskTable::COLUMN_END_DATE),
                'insertDate' => $builder->fieldDateTime(Table\Generated\AgentTaskTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $agentId, int $taskId, ContextInterface $context): mixed
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Agent\Task::class), 'findOneByAgentAndId'], [$agentId, $context->getUser()->getId(), $taskId], [
            'kind' => $builder->fieldValue(Service\TypeSystem\KindBuilder::build(Model\Backend\AgentTask::class)),
            'id' => $builder->fieldInteger(Table\Generated\AgentTaskTable::COLUMN_ID),
            'status' => $builder->fieldCallback(Table\Generated\AgentTaskTable::COLUMN_STATUS, fn($value) => Table\Agent\Task::getStatusName((int) $value)),
            'item' => $builder->fieldJson(Table\Generated\AgentTaskTable::COLUMN_ITEM),
            'steps' => $builder->fieldJson(Table\Generated\AgentTaskTable::COLUMN_STEPS),
            'message' => Table\Generated\AgentTaskTable::COLUMN_MESSAGE,
            'startDate' => $builder->fieldDateTime(Table\Generated\AgentTaskTable::COLUMN_START_DATE),
            'endDate' => $builder->fieldDateTime(Table\Generated\AgentTaskTable::COLUMN_END_DATE),
            'insertDate' => $builder->fieldDateTime(Table\Generated\AgentTaskTable::COLUMN_INSERT_DATE),
        ]);

        return $builder->build($definition);
    }
}
