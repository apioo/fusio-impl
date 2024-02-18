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

namespace Fusio\Impl\Backend\View\Event;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Subscription extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT], 'subscription');
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId() ?: 1);
        $condition->in('subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_STATUS, [Table\Event\Subscription::STATUS_ACTIVE]);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_ID,
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID,
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_USER_ID,
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            ])
            ->from('fusio_event_subscription', 'subscription')
            ->innerJoin('subscription', 'fusio_event', 'event', 'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID . ' = event.' . Table\Generated\EventTable::COLUMN_ID)
            ->orderBy('subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_event_subscription', 'subscription')
            ->innerJoin('subscription', 'fusio_event', 'event', 'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID . ' = event.' . Table\Generated\EventTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_ID),
                'eventId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID),
                'userId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_USER_ID),
                'endpoint' => Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals('subscription.' . Table\Generated\EventTable::COLUMN_ID, $id);
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('event.' . Table\Generated\EventTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId() ?: 1);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_ID,
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID,
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_USER_ID,
                'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            ])
            ->from('fusio_event_subscription', 'subscription')
            ->innerJoin('subscription', 'fusio_event', 'event', 'subscription.' . Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID . ' = event.' . Table\Generated\EventTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
            'id' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_ID),
            'eventId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_EVENT_ID),
            'userId' => $builder->fieldInteger(Table\Generated\EventSubscriptionTable::COLUMN_USER_ID),
            'endpoint' => Table\Generated\EventSubscriptionTable::COLUMN_ENDPOINT,
            'responses' => $builder->doCollection([$this->getTable(Table\Event\Response::class), 'getAllBySubscription'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_STATUS),
                'attempts' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_ATTEMPTS),
                'code' => $builder->fieldInteger(Table\Generated\EventResponseTable::COLUMN_CODE),
                'body' => Table\Generated\EventResponseTable::COLUMN_BODY,
                'executeDate' => $builder->fieldDateTime(Table\Generated\EventResponseTable::COLUMN_EXECUTE_DATE),
            ]),
        ]);

        return $builder->build($definition);
    }
}
