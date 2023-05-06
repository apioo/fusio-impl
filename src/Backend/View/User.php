<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
                'provider' => Table\Generated\UserTable::COLUMN_PROVIDER,
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

    public function getEntity(int $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\User::class), 'find'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
            'roleId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
            'planId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
            'provider' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_PROVIDER),
            'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
            'name' => Table\Generated\UserTable::COLUMN_NAME,
            'email' => Table\Generated\UserTable::COLUMN_EMAIL,
            'points' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_POINTS),
            'metadata' => $builder->fieldJson(Table\Generated\UserTable::COLUMN_METADATA),
            'scopes' => $builder->doColumn([$this->getTable(Table\User\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'plans' => $builder->doCollection([$this->getTable(Table\Plan::class), 'getActivePlansForUser'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
                'price' => $builder->fieldNumber(Table\Generated\PlanTable::COLUMN_PRICE),
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
