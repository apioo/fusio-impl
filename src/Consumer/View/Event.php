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

namespace Fusio\Impl\Consumer\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Event
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Event extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\EventColumn::tryFrom($filter->getSortBy(Table\Generated\EventTable::COLUMN_NAME) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::ASC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\EventTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\EventTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\EventTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\EventTable::COLUMN_STATUS, Table\Event::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Event::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Event::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\EventTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\EventTable::COLUMN_STATUS),
                'name' => Table\Generated\EventTable::COLUMN_NAME,
                'description' => Table\Generated\EventTable::COLUMN_DESCRIPTION,
                'metadata' => $builder->fieldJson(Table\Generated\EventTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Event::class), 'findOneByIdentifier'], [$context->getTenantId(), $context->getUser()->getCategoryId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\EventTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\EventTable::COLUMN_STATUS),
            'name' => Table\Generated\EventTable::COLUMN_NAME,
            'description' => Table\Generated\EventTable::COLUMN_DESCRIPTION,
            'schema' => Table\Generated\EventTable::COLUMN_EVENT_SCHEMA,
            'metadata' => $builder->fieldJson(Table\Generated\EventTable::COLUMN_METADATA),
        ]);

        return $builder->build($definition);
    }
}
