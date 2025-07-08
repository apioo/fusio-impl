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
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class User extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\UserColumn::tryFrom($filter->getSortBy(Table\Generated\UserTable::COLUMN_ID) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\UserTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->notEquals(Table\Generated\UserTable::COLUMN_STATUS, Table\User::STATUS_DELETED);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\User::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\User::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                'roleId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
                'planId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
                'identityId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_IDENTITY_ID),
                'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
                'name' => Table\Generated\UserTable::COLUMN_NAME,
                'email' => Table\Generated\UserTable::COLUMN_EMAIL,
                'points' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_POINTS),
                'metadata' => $builder->fieldJson(Table\Generated\UserTable::COLUMN_METADATA),
                'date' => $builder->fieldDateTime(Table\Generated\UserTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\User::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
            'roleId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
            'planId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
            'identityId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_IDENTITY_ID),
            'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
            'name' => Table\Generated\UserTable::COLUMN_NAME,
            'email' => Table\Generated\UserTable::COLUMN_EMAIL,
            'points' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_POINTS),
            'metadata' => $builder->fieldJson(Table\Generated\UserTable::COLUMN_METADATA),
            'scopes' => $builder->doColumn([$this->getTable(Table\User\Scope::class), 'getAvailableScopes'], [new Reference(Table\Generated\UserTable::COLUMN_TENANT_ID), new Reference(Table\Generated\UserTable::COLUMN_ID)], 'name'),
            'plans' => $builder->doCollection([$this->getTable(Table\Plan::class), 'getActivePlansForUser'], [new Reference(Table\Generated\UserTable::COLUMN_TENANT_ID), new Reference(Table\Generated\UserTable::COLUMN_ID)], [
                'id' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
                'price' => $builder->fieldCallback(Table\Generated\PlanTable::COLUMN_PRICE, function($value){
                    return round($value / 100, 2);
                }),
                'points' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_POINTS),
                'period' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_PERIOD_TYPE),
            ]),
            'apps' => $builder->doCollection([$this->getTable(Table\App::class), 'findByUserId'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
                'url' => Table\Generated\AppTable::COLUMN_URL,
                'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
                'date' => Table\Generated\AppTable::COLUMN_DATE,
            ]),
            'date' => $builder->fieldDateTime(Table\Generated\UserTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
