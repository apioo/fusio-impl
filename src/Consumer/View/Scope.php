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

namespace Fusio\Impl\Consumer\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = Condition::withAnd();
        $condition->equals('user_scope.' . Table\Generated\UserScopeTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->equals('scope.' . Table\Generated\ScopeTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('scope.' . Table\Generated\ScopeTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals('scope.' . Table\Generated\ScopeTable::COLUMN_STATUS, Table\Scope::STATUS_ACTIVE);

        $search = $filter->getSearch();
        if (!empty($search)) {
            $condition->equals('scope.' . Table\Generated\ScopeTable::COLUMN_NAME, '%' . $search . '%');
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'scope.' . Table\Generated\ScopeTable::COLUMN_ID,
                'scope.' . Table\Generated\ScopeTable::COLUMN_NAME,
                'scope.' . Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
                'scope.' . Table\Generated\ScopeTable::COLUMN_METADATA,
            ])
            ->from('fusio_user_scope', 'user_scope')
            ->innerJoin('user_scope', 'fusio_scope', 'scope', 'user_scope.' . Table\Generated\UserScopeTable::COLUMN_SCOPE_ID . ' = scope.' . Table\Generated\ScopeTable::COLUMN_ID)
            ->orderBy('scope.' . Table\Generated\ScopeTable::COLUMN_ID, 'ASC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_user_scope', 'user_scope')
            ->innerJoin('user_scope', 'fusio_scope', 'scope', 'user_scope.' . Table\Generated\UserScopeTable::COLUMN_SCOPE_ID . ' = scope.' . Table\Generated\ScopeTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
                'metadata' => $builder->fieldJson(Table\Generated\ScopeTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Scope::class), 'findOneByIdentifier'], [$context->getTenantId(), $context->getUser()->getCategoryId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
            'name' => Table\Generated\ScopeTable::COLUMN_NAME,
            'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
            'metadata' => $builder->fieldJson(Table\Generated\ScopeTable::COLUMN_METADATA),
        ]);

        return $builder->build($definition);
    }
}
