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

namespace Fusio\Impl\Consumer\View\User;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Grant
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Grant extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = Condition::withAnd();
        $condition->equals('user_grant.' . Table\Generated\UserGrantTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->equals('app.' . Table\Generated\AppTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('app.' . Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'user_grant.' . Table\Generated\UserGrantTable::COLUMN_ID,
                'user_grant.' . Table\Generated\UserGrantTable::COLUMN_APP_ID,
                'user_grant.' . Table\Generated\UserGrantTable::COLUMN_ALLOW,
                'user_grant.' . Table\Generated\UserGrantTable::COLUMN_DATE,
                'app.' . Table\Generated\AppTable::COLUMN_NAME,
                'app.' . Table\Generated\AppTable::COLUMN_URL,
            ])
            ->from('fusio_user_grant', 'user_grant')
            ->innerJoin('user_grant', 'fusio_app', 'app', 'user_grant.' . Table\Generated\UserGrantTable::COLUMN_APP_ID . ' = app.' . Table\Generated\AppTable::COLUMN_ID)
            ->orderBy('user_grant.' . Table\Generated\UserGrantTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_user_grant', 'user_grant')
            ->innerJoin('user_grant', 'fusio_app', 'app', 'user_grant.' . Table\Generated\UserGrantTable::COLUMN_APP_ID . ' = app.' . Table\Generated\AppTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\UserGrantTable::COLUMN_ID),
                'allow' => $builder->fieldInteger(Table\Generated\UserGrantTable::COLUMN_ALLOW),
                'createDate' => $builder->fieldDateTime(Table\Generated\UserGrantTable::COLUMN_DATE),
                'app' => [
                    'id' => $builder->fieldInteger(Table\Generated\UserGrantTable::COLUMN_APP_ID),
                    'name' => Table\Generated\AppTable::COLUMN_NAME,
                    'url' => Table\Generated\AppTable::COLUMN_URL,
                ],
            ]),
        ];

        return $builder->build($definition);
    }
}
