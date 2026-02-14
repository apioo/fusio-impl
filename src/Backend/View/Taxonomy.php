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
 * Taxonomy
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Taxonomy extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\TaxonomyColumn::tryFrom($filter->getSortBy(Table\Generated\TaxonomyTable::COLUMN_NAME) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::ASC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\TaxonomyTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\TaxonomyTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->in(Table\Generated\TaxonomyTable::COLUMN_STATUS, [Table\Taxonomy::STATUS_ACTIVE]);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Taxonomy::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Taxonomy::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\TaxonomyTable::COLUMN_ID),
                'parentId' => $builder->fieldInteger(Table\Generated\TaxonomyTable::COLUMN_PARENT_ID),
                'status' => $builder->fieldInteger(Table\Generated\TaxonomyTable::COLUMN_STATUS),
                'name' => Table\Generated\TaxonomyTable::COLUMN_NAME,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Taxonomy::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\TaxonomyTable::COLUMN_ID),
            'parentId' => $builder->fieldInteger(Table\Generated\TaxonomyTable::COLUMN_PARENT_ID),
            'status' => $builder->fieldInteger(Table\Generated\TaxonomyTable::COLUMN_STATUS),
            'name' => Table\Generated\TaxonomyTable::COLUMN_NAME,
        ]);

        return $builder->build($definition);
    }
}
