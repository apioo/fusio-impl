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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
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
    public function getEntity(int $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\User::class), 'find'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
            'roleId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
            'planId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
            'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
            'name' => Table\Generated\UserTable::COLUMN_NAME,
            'email' => Table\Generated\UserTable::COLUMN_EMAIL,
            'points' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_POINTS),
            'scopes' => $builder->doColumn([$this->getTable(Table\User\Scope::class), 'getAvailableScopes'], [new Reference('id'), true], 'name'),
            'plans' => $builder->doCollection([$this->getTable(Table\Plan::class), 'getActivePlansForUser'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
                'price' => $builder->fieldNumber(Table\Generated\PlanTable::COLUMN_PRICE),
                'points' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_POINTS),
                'period' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_PERIOD_TYPE),
            ]),
            'metadata' => $builder->fieldJson(Table\Generated\UserTable::COLUMN_METADATA),
            'date' => $builder->fieldDateTime(Table\Generated\UserTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
