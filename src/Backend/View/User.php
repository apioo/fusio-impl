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
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class User extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\UserTable::COLUMN_ID;
        }

        if ($sortOrder === null) {
            $sortOrder = OrderBy::DESC;
        }

        $condition = Condition::withAnd();
        $condition->notEquals(Table\Generated\UserTable::COLUMN_STATUS, Table\User::STATUS_DELETED);

        if (!empty($search)) {
            $condition->like(Table\Generated\UserTable::COLUMN_NAME, '%' . $search . '%');
        }

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

    public function getEntity(string $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\User::class), 'findOneByIdentifier'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
            'roleId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
            'planId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
            'identityId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_IDENTITY_ID),
            'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
            'name' => Table\Generated\UserTable::COLUMN_NAME,
            'email' => Table\Generated\UserTable::COLUMN_EMAIL,
            'points' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_POINTS),
            'metadata' => $builder->fieldJson(Table\Generated\UserTable::COLUMN_METADATA),
            'scopes' => $builder->doColumn([$this->getTable(Table\User\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'plans' => $builder->doCollection([$this->getTable(Table\Plan::class), 'getActivePlansForUser'], [new Reference('id')], [
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
