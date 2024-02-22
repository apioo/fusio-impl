<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Rate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Rate extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = $filter->getSortBy(Table\Generated\RateTable::COLUMN_PRIORITY);
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\RateTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\RateTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->in(Table\Generated\RateTable::COLUMN_STATUS, [Table\Rate::STATUS_ACTIVE]);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Rate::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Rate::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\RateTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\RateTable::COLUMN_STATUS),
                'priority' => $builder->fieldInteger(Table\Generated\RateTable::COLUMN_PRIORITY),
                'name' => Table\Generated\RateTable::COLUMN_NAME,
                'rateLimit' => Table\Generated\RateTable::COLUMN_RATE_LIMIT,
                'timespan' => Table\Generated\RateTable::COLUMN_TIMESPAN,
                'metadata' => $builder->fieldJson(Table\Generated\RateTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Rate::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\RateTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\RateTable::COLUMN_STATUS),
            'priority' => $builder->fieldInteger(Table\Generated\RateTable::COLUMN_PRIORITY),
            'name' => Table\Generated\RateTable::COLUMN_NAME,
            'rateLimit' => Table\Generated\RateTable::COLUMN_RATE_LIMIT,
            'timespan' => Table\Generated\RateTable::COLUMN_TIMESPAN,
            'metadata' => $builder->fieldJson(Table\Generated\RateTable::COLUMN_METADATA),
            'allocation' => $builder->doCollection([$this->getTable(Table\Rate\Allocation::class), 'findByRateId'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_ID),
                'rateId' => $builder->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_RATE_ID),
                'operationId' => $builder->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_OPERATION_ID),
                'userId' => $builder->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_USER_ID),
                'planId' => $builder->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_PLAN_ID),
                'appId' => $builder->fieldInteger(Table\Generated\RateAllocationTable::COLUMN_APP_ID),
                'authenticated' => $builder->fieldBoolean(Table\Generated\RateAllocationTable::COLUMN_AUTHENTICATED),
            ]),
        ]);

        return $builder->build($definition);
    }
}
