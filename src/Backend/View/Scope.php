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
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = $filter->getSortBy(Table\Generated\ScopeTable::COLUMN_ID);
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\ScopeTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\ScopeTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\ScopeTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId() ?: 1);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Scope::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Scope::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
                'metadata' => $builder->fieldJson(Table\Generated\ScopeTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Scope::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
            'name' => Table\Generated\ScopeTable::COLUMN_NAME,
            'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
            'metadata' => $builder->fieldJson(Table\Generated\ScopeTable::COLUMN_METADATA),
            'operations' => $builder->doCollection([$this->getTable(Table\Scope\Operation::class), 'findByScopeId'], [new Reference('id'), 0, 1024], [
                'id' => $builder->fieldInteger(Table\Generated\ScopeOperationTable::COLUMN_ID),
                'scopeId' => $builder->fieldInteger(Table\Generated\ScopeOperationTable::COLUMN_SCOPE_ID),
                'operationId' => $builder->fieldInteger(Table\Generated\ScopeOperationTable::COLUMN_OPERATION_ID),
                'allow' => $builder->fieldBoolean(Table\Generated\ScopeOperationTable::COLUMN_ALLOW),
            ]),
        ]);

        return $builder->build($definition);
    }

    public function getCategories()
    {
        $builder = new Builder($this->connection);

        $definition = [
            'categories' => $builder->doCollection([$this->getTable(Table\Category::class), 'findAll'], [null, 0, 1024, 'name', OrderBy::ASC], [
                'id' => $builder->fieldInteger(Table\Generated\CategoryTable::COLUMN_ID),
                'name' => Table\Generated\CategoryTable::COLUMN_NAME,
                'scopes' => $builder->doCollection([$this->getTable(Table\Scope::class), 'findByCategoryId'], [new Reference('id'), 0, 1024, 'name', OrderBy::ASC], [
                    'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                    'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                    'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
                ])
            ]),
        ];

        return $builder->build($definition);
    }
}
