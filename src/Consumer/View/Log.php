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
use Fusio\Impl\Backend\Filter\Log\LogQueryFilter;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Log
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Log extends ViewAbstract
{
    public function getCollection(LogQueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\LogColumn::tryFrom($filter->getSortBy(Table\Generated\LogTable::COLUMN_ID) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\LogTable::COLUMN_PATH]);
        $condition->equals(Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\LogTable::COLUMN_USER_ID, $context->getUser()->getId());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Log::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Log::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_ID),
                'appId' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_APP_ID),
                'ip' => Table\Generated\LogTable::COLUMN_IP,
                'userAgent' => Table\Generated\LogTable::COLUMN_USER_AGENT,
                'method' => Table\Generated\LogTable::COLUMN_METHOD,
                'path' => Table\Generated\LogTable::COLUMN_PATH,
                'date' => $builder->fieldDateTime(Table\Generated\LogTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $logId, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\LogTable::COLUMN_ID, $logId);
        $condition->equals(Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\LogTable::COLUMN_USER_ID, $context->getUser()->getId());

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Log::class), 'findOneBy'], [$condition], [
            'id' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_ID),
            'appId' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_APP_ID),
            'ip' => Table\Generated\LogTable::COLUMN_IP,
            'userAgent' => Table\Generated\LogTable::COLUMN_USER_AGENT,
            'method' => Table\Generated\LogTable::COLUMN_METHOD,
            'path' => Table\Generated\LogTable::COLUMN_PATH,
            'header' => Table\Generated\LogTable::COLUMN_HEADER,
            'body' => Table\Generated\LogTable::COLUMN_BODY,
            'date' => $builder->fieldDateTime(Table\Generated\LogTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
