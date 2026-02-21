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

namespace Fusio\Impl\Backend\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
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
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\AgentColumn::tryFrom($filter->getSortBy(Table\Generated\AgentTable::COLUMN_NAME) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::ASC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\AgentTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\AgentTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->in(Table\Generated\AgentTable::COLUMN_STATUS, [Table\Agent::STATUS_ACTIVE]);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Agent::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Agent::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_STATUS),
                'type' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_TYPE),
                'name' => Table\Generated\AgentTable::COLUMN_NAME,
                'description' => Table\Generated\AgentTable::COLUMN_DESCRIPTION,
                'outgoing' => Table\Generated\AgentTable::COLUMN_OUTGOING,
                'action' => Table\Generated\AgentTable::COLUMN_ACTION,
                'metadata' => $builder->fieldJson(Table\Generated\AgentTable::COLUMN_METADATA),
                'insertDate' => $builder->fieldDateTime(Table\Generated\AgentTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Agent::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_STATUS),
            'type' => $builder->fieldInteger(Table\Generated\AgentTable::COLUMN_TYPE),
            'name' => Table\Generated\AgentTable::COLUMN_NAME,
            'description' => Table\Generated\AgentTable::COLUMN_DESCRIPTION,
            'introduction' => Table\Generated\AgentTable::COLUMN_INTRODUCTION,
            'tools' => $builder->fieldJson(Table\Generated\AgentTable::COLUMN_TOOLS),
            'outgoing' => Table\Generated\AgentTable::COLUMN_OUTGOING,
            'action' => Table\Generated\AgentTable::COLUMN_ACTION,
            'metadata' => $builder->fieldJson(Table\Generated\AgentTable::COLUMN_METADATA),
            'insertDate' => $builder->fieldDateTime(Table\Generated\AgentTable::COLUMN_INSERT_DATE),
        ]);

        return $builder->build($definition);
    }
}
