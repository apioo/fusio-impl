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

namespace Fusio\Impl\Backend\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\Audit\AuditQueryFilter;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Audit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Audit extends ViewAbstract
{
    public function getCollection(AuditQueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\AuditColumn::tryFrom($filter->getSortBy(Table\Generated\AuditTable::COLUMN_ID) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\AuditTable::COLUMN_MESSAGE, DateQueryFilter::COLUMN_DATE => Table\Generated\AuditTable::COLUMN_DATE]);
        $condition->equals(Table\Generated\AuditTable::COLUMN_TENANT_ID, $context->getTenantId());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Audit::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Audit::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\AuditTable::COLUMN_ID),
                'event' => Table\Generated\AuditTable::COLUMN_EVENT,
                'ip' => Table\Generated\AuditTable::COLUMN_IP,
                'message' => Table\Generated\AuditTable::COLUMN_MESSAGE,
                'date' => $builder->fieldDateTime(Table\Generated\AuditTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Audit::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\AuditTable::COLUMN_ID),
            'app' => $builder->doEntity([$this->getTable(Table\App::class), 'find'], [new Reference('app_id')], [
                'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
            ]),
            'user' => $builder->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
                'name' => Table\Generated\UserTable::COLUMN_NAME,
            ]),
            'refId' => Table\Generated\AuditTable::COLUMN_REF_ID,
            'event' => Table\Generated\AuditTable::COLUMN_EVENT,
            'ip' => Table\Generated\AuditTable::COLUMN_IP,
            'message' => Table\Generated\AuditTable::COLUMN_MESSAGE,
            'content' => $builder->fieldJson(Table\Generated\AuditTable::COLUMN_CONTENT),
            'date' => $builder->fieldDateTime(Table\Generated\AuditTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
