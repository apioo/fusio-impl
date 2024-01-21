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

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
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
    public function getCollection(int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null, ?string $tenantId = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\PlanTable::COLUMN_PRICE;
        }

        if ($sortOrder === null) {
            $sortOrder = OrderBy::ASC;
        }

        $condition = Condition::withAnd();
        if (!empty($tenantId)) {
            $condition->equals(Table\Generated\PlanTable::COLUMN_TENANT_ID, $tenantId);
        }
        $condition->equals(Table\Generated\PlanTable::COLUMN_STATUS, Table\Plan::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\PlanTable::COLUMN_NAME, '%' . $search . '%');
        }

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

    public function getEntity(string $id, ?string $tenantId = null)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Plan::class), 'findOneByIdentifier'], [$id, $tenantId], [
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
            'scopes' => $builder->doColumn([$this->getTable(Table\Plan\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
        ]);

        return $builder->build($definition);
    }
}
