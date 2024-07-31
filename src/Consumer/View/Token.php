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
use Fusio\Impl\Backend\Filter\App\Token\TokenQueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Token extends ViewAbstract
{
    public function getCollection(TokenQueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = $filter->getSortBy(Table\Generated\TokenTable::COLUMN_ID);
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([]);
        $condition->equals(Table\Generated\TokenTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\TokenTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\TokenTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->equals(Table\Generated\TokenTable::COLUMN_STATUS, Table\Token::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Token::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $builder->doCollection([$this->getTable(Table\Token::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => Table\Generated\TokenTable::COLUMN_ID,
                'status' => Table\Generated\TokenTable::COLUMN_STATUS,
                'name' => Table\Generated\TokenTable::COLUMN_NAME,
                'scopes' => $builder->fieldCsv(Table\Generated\TokenTable::COLUMN_SCOPE),
                'ip' => Table\Generated\TokenTable::COLUMN_IP,
                'expire' => $builder->fieldDateTime(Table\Generated\TokenTable::COLUMN_EXPIRE),
                'date' => $builder->fieldDateTime(Table\Generated\TokenTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $tokenId, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\TokenTable::COLUMN_ID, $tokenId);
        $condition->equals(Table\Generated\TokenTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\TokenTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\TokenTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->equals(Table\Generated\TokenTable::COLUMN_STATUS, Table\Plan::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Token::class), 'findOneBy'], [$condition], [
            'id' => Table\Generated\TokenTable::COLUMN_ID,
            'status' => Table\Generated\TokenTable::COLUMN_STATUS,
            'name' => Table\Generated\TokenTable::COLUMN_NAME,
            'scopes' => $builder->fieldCsv(Table\Generated\TokenTable::COLUMN_SCOPE),
            'ip' => Table\Generated\TokenTable::COLUMN_IP,
            'expire' => $builder->fieldDateTime(Table\Generated\TokenTable::COLUMN_EXPIRE),
            'date' => $builder->fieldDateTime(Table\Generated\TokenTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
