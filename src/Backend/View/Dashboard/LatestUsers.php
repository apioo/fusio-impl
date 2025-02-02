<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Backend\View\Dashboard;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * LatestUsers
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LatestUsers extends ViewAbstract
{
    public function getView(ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\UserTable::COLUMN_STATUS, Table\User::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'usr.' . Table\Generated\UserTable::COLUMN_ID,
                'usr.' . Table\Generated\UserTable::COLUMN_STATUS,
                'usr.' . Table\Generated\UserTable::COLUMN_NAME,
                'usr.' . Table\Generated\UserTable::COLUMN_DATE,
            ])
            ->from('fusio_user', 'usr')
            ->orderBy('usr.' . Table\Generated\UserTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult(0)
            ->setMaxResults(6);

        $builder = new Builder($this->connection);

        $definition = [
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
                'name' => Table\Generated\UserTable::COLUMN_NAME,
                'date' => $builder->fieldDateTime(Table\Generated\UserTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }
}
