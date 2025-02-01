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
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
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

        $condition = Condition::withAnd();
        $condition->equals('webhook.' . Table\Generated\WebhookTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->in('webhook.' . Table\Generated\WebhookTable::COLUMN_STATUS, [Table\Webhook::STATUS_ACTIVE]);
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $search = $filter->getSearch();
        if (!empty($search)) {
            $condition->like('webhook.' . Table\Generated\WebhookTable::COLUMN_NAME, '%' . $search . '%');
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'webhook.' . Table\Generated\WebhookTable::COLUMN_ID,
                'webhook.' . Table\Generated\WebhookTable::COLUMN_STATUS,
                'webhook.' . Table\Generated\WebhookTable::COLUMN_NAME,
                'webhook.' . Table\Generated\WebhookTable::COLUMN_ENDPOINT,
                'event.' . Table\Generated\EventTable::COLUMN_NAME . ' AS event_name',
            ])
            ->from('fusio_webhook', 'webhook')
            ->innerJoin('webhook', 'fusio_event', 'event', 'webhook.' . Table\Generated\WebhookTable::COLUMN_EVENT_ID . ' = event.' . Table\Generated\EventTable::COLUMN_ID)
            ->orderBy('webhook.' . Table\Generated\WebhookTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_webhook', 'webhook')
            ->innerJoin('webhook', 'fusio_event', 'event', 'webhook.' . Table\Generated\WebhookTable::COLUMN_EVENT_ID . ' = event.' . Table\Generated\EventTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_STATUS),
                'name' => Table\Generated\WebhookTable::COLUMN_NAME,
                'event' => 'event_name',
                'endpoint' => Table\Generated\WebhookTable::COLUMN_ENDPOINT,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $webhookId, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals('webhook.' . Table\Generated\WebhookTable::COLUMN_ID, $webhookId);
        $condition->equals('webhook.' . Table\Generated\WebhookTable::COLUMN_USER_ID, $context->getUser()->getId());
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'webhook.' . Table\Generated\WebhookTable::COLUMN_ID,
                'webhook.' . Table\Generated\WebhookTable::COLUMN_STATUS,
                'webhook.' . Table\Generated\WebhookTable::COLUMN_NAME,
                'webhook.' . Table\Generated\WebhookTable::COLUMN_ENDPOINT,
                'event.' . Table\Generated\EventTable::COLUMN_NAME . ' AS event_name',
            ])
            ->from('fusio_webhook', 'webhook')
            ->innerJoin('webhook', 'fusio_event', 'event', 'webhook.' . Table\Generated\WebhookTable::COLUMN_EVENT_ID . ' = event.' . Table\Generated\EventTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
            'id' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\WebhookTable::COLUMN_STATUS),
            'name' => Table\Generated\WebhookTable::COLUMN_NAME,
            'event' => 'event_name',
            'endpoint' => Table\Generated\WebhookTable::COLUMN_ENDPOINT,
            'responses' => $builder->doCollection([$this->getTable(Table\Webhook\Response::class), 'getAllByWebhook'], [new Reference(Table\Generated\WebhookTable::COLUMN_ID)], [
                'status' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_STATUS),
                'attempts' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_ATTEMPTS),
                'code' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_CODE),
                'body' => $builder->fieldInteger(Table\Generated\WebhookResponseTable::COLUMN_BODY),
                'executeDate' => $builder->fieldDateTime(Table\Generated\WebhookResponseTable::COLUMN_EXECUTE_DATE),
            ]),
        ]);

        return $builder->build($definition);
    }
}
