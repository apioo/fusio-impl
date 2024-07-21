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
 * Test
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Test extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = $filter->getSortBy(Table\Generated\TestTable::COLUMN_ID);
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\TestTable::COLUMN_MESSAGE]);
        $condition->equals(Table\Generated\TestTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\TestTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId() ?: 1);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Test::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Test::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\TestTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\TestTable::COLUMN_STATUS),
                'operationName' => $builder->doValue([$this->getTable(Table\Operation::class), 'find'], [new Reference(Table\Generated\TestTable::COLUMN_OPERATION_ID)], Table\Generated\OperationTable::COLUMN_NAME),
                'message' => Table\Generated\TestTable::COLUMN_MESSAGE,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Test::class), 'findOneByTenantAndId'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\TestTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\TestTable::COLUMN_STATUS),
            'operationName' => $builder->doValue([$this->getTable(Table\Operation::class), 'find'], [new Reference(Table\Generated\TestTable::COLUMN_OPERATION_ID)], Table\Generated\OperationTable::COLUMN_NAME),
            'message' => Table\Generated\TestTable::COLUMN_MESSAGE,
            'response' => Table\Generated\TestTable::COLUMN_RESPONSE,
            'config' => [
                'uriFragments' => Table\Generated\TestTable::COLUMN_URI_FRAGMENTS,
                'parameters' => Table\Generated\TestTable::COLUMN_PARAMETERS,
                'headers' => Table\Generated\TestTable::COLUMN_HEADERS,
                'body' => $builder->fieldJson(Table\Generated\TestTable::COLUMN_BODY),
            ],
        ]);

        return $builder->build($definition);
    }
}
