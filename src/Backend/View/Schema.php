<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Schema extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = $filter->getSortBy(Table\Generated\SchemaTable::COLUMN_ID);
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\SchemaTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\SchemaTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\SchemaTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\SchemaTable::COLUMN_STATUS, Table\Schema::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Schema::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Schema::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
                'name' => Table\Generated\SchemaTable::COLUMN_NAME,
                'metadata' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Schema::class), 'findOneByIdentifier'], [$context->getTenantId(), $context->getUser()->getCategoryId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
            'name' => Table\Generated\SchemaTable::COLUMN_NAME,
            'metadata' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_METADATA),
            'source' => Table\Generated\SchemaTable::COLUMN_SOURCE,
            'form' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_FORM),
        ]);

        return $builder->build($definition);
    }

    public function getEntityWithForm(string $name, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Schema::class), 'findOneByTenantAndName'], [$context->getTenantId(), null, $name], [
            'id' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
            'name' => Table\Generated\SchemaTable::COLUMN_NAME,
            'form' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_FORM),
        ]);

        return $builder->build($definition);
    }
}
