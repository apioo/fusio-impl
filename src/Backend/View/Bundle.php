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
 * Bundle
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Bundle extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\BundleColumn::tryFrom($filter->getSortBy(Table\Generated\BundleTable::COLUMN_NAME) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::ASC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\BundleTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\BundleTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->in(Table\Generated\BundleTable::COLUMN_STATUS, [Table\Bundle::STATUS_ACTIVE]);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Bundle::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Bundle::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\BundleTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\BundleTable::COLUMN_STATUS),
                'name' => Table\Generated\BundleTable::COLUMN_NAME,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Bundle::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\BundleTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\BundleTable::COLUMN_STATUS),
            'name' => Table\Generated\BundleTable::COLUMN_NAME,
            'config' => $builder->fieldJson(Table\Generated\BundleTable::COLUMN_CONFIG),
        ]);

        return $builder->build($definition);
    }
}
