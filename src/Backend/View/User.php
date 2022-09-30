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
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
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
            $sortOrder = Sql::SORT_DESC;
        }

        $condition = new Condition();
        $condition->notEquals(Table\Generated\UserTable::COLUMN_STATUS, Table\User::STATUS_DELETED);

        if (!empty($search)) {
            $condition->like(Table\Generated\UserTable::COLUMN_NAME, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\User::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\User::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                'roleId' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
                'planId' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
                'provider' => Table\Generated\UserTable::COLUMN_PROVIDER,
                'status' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
                'name' => Table\Generated\UserTable::COLUMN_NAME,
                'email' => Table\Generated\UserTable::COLUMN_EMAIL,
                'date' => $this->fieldDateTime(Table\Generated\UserTable::COLUMN_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id, array $userAttributes = null)
    {
        $definition = $this->doEntity([$this->getTable(Table\User::class), 'find'], [$id], [
            'id' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
            'roleId' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
            'planId' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
            'provider' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_PROVIDER),
            'status' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
            'name' => Table\Generated\UserTable::COLUMN_NAME,
            'email' => Table\Generated\UserTable::COLUMN_EMAIL,
            'points' => $this->fieldInteger(Table\Generated\UserTable::COLUMN_POINTS),
            'scopes' => $this->doColumn([$this->getTable(Table\User\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'plans' => $this->doCollection([$this->getTable(Table\Plan::class), 'getActivePlansForUser'], [new Reference('id')], [
                'id' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
                'price' => $this->fieldNumber(Table\Generated\PlanTable::COLUMN_PRICE),
                'points' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_POINTS),
                'period' => $this->fieldInteger(Table\Generated\PlanTable::COLUMN_PERIOD_TYPE),
            ]),
            'apps' => $this->doCollection([$this->getTable(Table\App::class), 'findByUserId'], [new Reference('id')], [
                'id' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
                'url' => Table\Generated\AppTable::COLUMN_URL,
                'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
                'date' => Table\Generated\AppTable::COLUMN_DATE,
            ]),
            'attributes' => $this->doCollection([$this->getTable(Table\User\Attribute::class), 'findByUserId'], [new Reference('id')], [
                'name' => Table\Generated\UserAttributeTable::COLUMN_NAME,
                'value' => Table\Generated\UserAttributeTable::COLUMN_VALUE,
            ], null, function (array $result) use ($userAttributes) {
                $values = [];
                foreach ($result as $row) {
                    $values[$row['name']] = $row['value'];
                }

                $data = [];
                if (!empty($userAttributes)) {
                    foreach ($userAttributes as $name) {
                        $data[$name] = $values[$name] ?? null;
                    }
                }

                return $data ?: null;
            }),
            'date' => $this->fieldDateTime(Table\Generated\UserTable::COLUMN_DATE),
        ]);

        return $this->build($definition);
    }
}
