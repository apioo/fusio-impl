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

namespace Fusio\Impl\Backend\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Webhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Webhook extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\WebhookColumn::tryFrom($filter->getSortBy(Table\Generated\WebhookTable::COLUMN_ID) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\WebhookTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\WebhookTable::COLUMN_TENANT_ID, $context->getTenantId());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Webhook::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Webhook::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_ID),
                'eventId' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_EVENT_ID),
                'userId' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_USER_ID),
                'name' => Table\Generated\WebhookTable::COLUMN_NAME,
                'endpoint' => Table\Generated\WebhookTable::COLUMN_ENDPOINT,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Webhook::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_ID),
            'eventId' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_EVENT_ID),
            'userId' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_USER_ID),
            'name' => Table\Generated\WebhookTable::COLUMN_NAME,
            'endpoint' => Table\Generated\WebhookTable::COLUMN_ENDPOINT,
            'responses' => $builder->doCollection([$this->getTable(Table\Webhook\Response::class), 'getAllByWebhook'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_STATUS),
                'attempts' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_ATTEMPTS),
                'code' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_CODE),
                'body' => Table\Generated\WebhookResponseTable::COLUMN_BODY,
                'executeDate' => $builder->fieldDateTime(Table\Generated\WebhookResponseTable::COLUMN_EXECUTE_DATE),
            ]),
        ]);

        return $builder->build($definition);
    }
}
