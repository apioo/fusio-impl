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

namespace Fusio\Impl\Backend\View\Schema;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Commit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Commit extends ViewAbstract
{
    public function getCollection(int $schemaId, QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\SchemaCommitColumn::tryFrom($filter->getSortBy(Table\Generated\SchemaCommitTable::COLUMN_ID) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\SchemaCommitTable::COLUMN_SOURCE]);
        $condition->equals(Table\Generated\SchemaCommitTable::COLUMN_SCHEMA_ID, $schemaId);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Schema\Commit::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Schema\Commit::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\SchemaCommitTable::COLUMN_ID),
                'user' => $builder->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference(Table\Generated\SchemaCommitTable::COLUMN_USER_ID)], [
                    'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                    'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
                    'name' => Table\Generated\UserTable::COLUMN_NAME,
                ]),
                'commitHash' => $builder->fieldInteger(Table\Generated\SchemaCommitTable::COLUMN_COMMIT_HASH),
                'schema' => $builder->fieldJson(Table\Generated\SchemaCommitTable::COLUMN_SOURCE),
                'insertDate' => $builder->fieldDateTime(Table\Generated\SchemaCommitTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }
}
