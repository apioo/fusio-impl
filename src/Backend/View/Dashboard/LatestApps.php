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

namespace Fusio\Impl\Backend\View\Dashboard;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * LatestApps
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LatestApps extends ViewAbstract
{
    public function getView(ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'app.' . Table\Generated\AppTable::COLUMN_ID,
                'app.' . Table\Generated\AppTable::COLUMN_NAME,
                'app.' . Table\Generated\AppTable::COLUMN_DATE
            ])
            ->from('fusio_app', 'app')
            ->orderBy('app.' . Table\Generated\AppTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult(0)
            ->setMaxResults(6);

        $builder = new Builder($this->connection);

        $definition = [
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
                'date' => $builder->fieldDateTime(Table\Generated\AppTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }
}
