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
use PSX\Nested\Reference;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Plan extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\PlanColumn::tryFrom($filter->getSortBy(Table\Generated\PlanTable::COLUMN_PRICE) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::ASC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\PlanTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\PlanTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\PlanTable::COLUMN_STATUS, Table\Plan::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Plan::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Plan::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_STATUS),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
                'description' => Table\Generated\PlanTable::COLUMN_DESCRIPTION,
                'price' => $builder->fieldCallback(Table\Generated\PlanTable::COLUMN_PRICE, function($value){
                    return round($value / 100, 2);
                }),
                'points' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_POINTS),
                'period' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_PERIOD_TYPE),
                'externalId' => Table\Generated\PlanTable::COLUMN_EXTERNAL_ID,
                'metadata' => $builder->fieldJson(Table\Generated\PlanTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Plan::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_STATUS),
            'name' => Table\Generated\PlanTable::COLUMN_NAME,
            'description' => Table\Generated\PlanTable::COLUMN_DESCRIPTION,
            'price' => $builder->fieldCallback(Table\Generated\PlanTable::COLUMN_PRICE, function($value){
                return round($value / 100, 2);
            }),
            'points' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_POINTS),
            'period' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_PERIOD_TYPE),
            'externalId' => Table\Generated\PlanTable::COLUMN_EXTERNAL_ID,
            'scopes' => $builder->doColumn([$this->getTable(Table\Plan\Scope::class), 'getAvailableScopes'], [new Reference(Table\Generated\PlanTable::COLUMN_TENANT_ID), new Reference(Table\Generated\PlanTable::COLUMN_ID)], 'name'),
        ]);

        return $builder->build($definition);
    }
}
