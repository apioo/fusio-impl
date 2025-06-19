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

namespace Fusio\Impl\Consumer\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class App extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\AppColumn::tryFrom($filter->getSortBy(Table\Generated\AppTable::COLUMN_ID) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\AppTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\App::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $builder->doCollection([$this->getTable(Table\App::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'userId' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_USER_ID),
                'status' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
                'url' => Table\Generated\AppTable::COLUMN_URL,
                'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
                'metadata' => $builder->fieldJson(Table\Generated\AppTable::COLUMN_METADATA),
                'date' => $builder->fieldDateTime(Table\Generated\AppTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $appId, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_ID, $appId);
        $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\App::class), 'findOneBy'], [$condition], [
            'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
            'userId' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_USER_ID),
            'status' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
            'name' => Table\Generated\AppTable::COLUMN_NAME,
            'url' => Table\Generated\AppTable::COLUMN_URL,
            'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
            'appSecret' => Table\Generated\AppTable::COLUMN_APP_SECRET,
            'metadata' => $builder->fieldJson(Table\Generated\AppTable::COLUMN_METADATA),
            'scopes' => $builder->doColumn([$this->getTable(Table\App\Scope::class), 'getAvailableScopes'], [$context->getTenantId(), new Reference('id')], 'name'),
            'tokens' => $builder->doCollection([$this->getTable(Table\Token::class), 'getTokensByApp'], [$context->getTenantId(), new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\TokenTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\TokenTable::COLUMN_STATUS),
                'name' => Table\Generated\TokenTable::COLUMN_NAME,
                'scope' => $builder->fieldCsv(Table\Generated\TokenTable::COLUMN_SCOPE),
                'ip' => Table\Generated\TokenTable::COLUMN_IP,
                'expire' => Table\Generated\TokenTable::COLUMN_EXPIRE,
                'date' => $builder->fieldDateTime(Table\Generated\TokenTable::COLUMN_DATE),
            ]),
            'date' => $builder->fieldDateTime(Table\Generated\AppTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }

    public function getEntityByAppKey(?string $tenantId, string $appKey, string $scope)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);
        $condition->equals(Table\Generated\AppTable::COLUMN_APP_KEY, $appKey);

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\App::class), 'findOneBy'], [$condition], [
            'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
            'name' => Table\Generated\AppTable::COLUMN_NAME,
            'url' => Table\Generated\AppTable::COLUMN_URL,
            'scopes' => $builder->doCollection([$this->getTable(Table\App\Scope::class), 'getValidScopes'], [$tenantId, new Reference('id'), explode(',', $scope), ['backend']], [
                'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
            ]),
        ]);

        return $builder->build($definition);
    }
}
