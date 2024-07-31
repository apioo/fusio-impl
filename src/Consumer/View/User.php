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

namespace Fusio\Impl\Consumer\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
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
    public function getEntity(ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\UserTable::COLUMN_ID, $context->getUser()->getId());
        $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $context->getTenantId());

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\User::class), 'findOneBy'], [$condition], [
            'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
            'roleId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ROLE_ID),
            'planId' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_PLAN_ID),
            'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
            'name' => Table\Generated\UserTable::COLUMN_NAME,
            'email' => Table\Generated\UserTable::COLUMN_EMAIL,
            'points' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_POINTS),
            'scopes' => $builder->doColumn([$this->getTable(Table\User\Scope::class), 'getAvailableScopes'], [new Reference(Table\Generated\UserTable::COLUMN_TENANT_ID), new Reference(Table\Generated\UserTable::COLUMN_ID), true], 'name'),
            'plans' => $builder->doCollection([$this->getTable(Table\Plan::class), 'getActivePlansForUser'], [new Reference(Table\Generated\UserTable::COLUMN_TENANT_ID), new Reference(Table\Generated\UserTable::COLUMN_ID)], [
                'id' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_ID),
                'name' => Table\Generated\PlanTable::COLUMN_NAME,
                'price' => $builder->fieldCallback(Table\Generated\PlanTable::COLUMN_PRICE, function($value){
                    return round($value / 100, 2);
                }),
                'points' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_POINTS),
                'period' => $builder->fieldInteger(Table\Generated\PlanTable::COLUMN_PERIOD_TYPE),
            ]),
            'metadata' => $builder->fieldJson(Table\Generated\UserTable::COLUMN_METADATA),
            'date' => $builder->fieldDateTime(Table\Generated\UserTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
