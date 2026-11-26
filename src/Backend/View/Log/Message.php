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

namespace Fusio\Impl\Backend\View\Log;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Message
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Message extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context): mixed
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = $filter->getCondition($this->getTable(Table\Log\Message::class), [QueryFilter::COLUMN_SEARCH => Table\Generated\LogMessageColumn::MESSAGE], 'message');
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'message.' . Table\Generated\LogMessageTable::COLUMN_ID,
                'message.' . Table\Generated\LogMessageTable::COLUMN_LOG_ID,
                'message.' . Table\Generated\LogMessageTable::COLUMN_LEVEL,
                'message.' . Table\Generated\LogMessageTable::COLUMN_MESSAGE,
                'message.' . Table\Generated\LogMessageTable::COLUMN_CONTEXT,
                'message.' . Table\Generated\LogMessageTable::COLUMN_INSERT_DATE,
            ])
            ->from('fusio_log_message', 'message')
            ->innerJoin('message', 'fusio_log', 'log', 'message.' . Table\Generated\LogMessageTable::COLUMN_LOG_ID . ' = log.' . Table\Generated\LogTable::COLUMN_ID)
            ->orderBy('message.' . Table\Generated\LogMessageTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_log_message', 'message')
            ->innerJoin('message', 'fusio_log', 'log', 'message.' . Table\Generated\LogMessageTable::COLUMN_LOG_ID . ' = log.' . Table\Generated\LogTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = [
            'kind' => $builder->fieldValue(Service\TypeSystem\KindBuilder::build(Model\Backend\LogMessageCollection::class)),
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'kind' => $builder->fieldValue(Service\TypeSystem\KindBuilder::build(Model\Backend\LogMessage::class)),
                'id' => $builder->fieldInteger(Table\Generated\LogMessageTable::COLUMN_ID),
                'logId' => $builder->fieldInteger(Table\Generated\LogMessageTable::COLUMN_LOG_ID),
                'level' => $builder->fieldInteger(Table\Generated\LogMessageTable::COLUMN_LEVEL),
                'message' => Table\Generated\LogMessageTable::COLUMN_MESSAGE,
                'context' => $builder->fieldJson(Table\Generated\LogMessageTable::COLUMN_CONTEXT),
                'insertDate' => $builder->fieldDateTime(Table\Generated\LogMessageTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id, ContextInterface $context): mixed
    {
        $condition = Condition::withAnd();
        $condition->equals('message.' . Table\Generated\LogMessageTable::COLUMN_ID, $id);
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'message.' . Table\Generated\LogMessageTable::COLUMN_ID,
                'message.' . Table\Generated\LogMessageTable::COLUMN_LOG_ID,
                'message.' . Table\Generated\LogMessageTable::COLUMN_LEVEL,
                'message.' . Table\Generated\LogMessageTable::COLUMN_MESSAGE,
                'message.' . Table\Generated\LogMessageTable::COLUMN_CONTEXT,
                'message.' . Table\Generated\LogMessageTable::COLUMN_INSERT_DATE,
            ])
            ->from('fusio_log_message', 'message')
            ->innerJoin('message', 'fusio_log', 'log', 'message.' . Table\Generated\LogMessageTable::COLUMN_LOG_ID . ' = log.' . Table\Generated\LogTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
            'kind' => $builder->fieldValue(Service\TypeSystem\KindBuilder::build(Model\Backend\LogMessage::class)),
            'id' => $builder->fieldInteger(Table\Generated\LogMessageTable::COLUMN_ID),
            'logId' => $builder->fieldInteger(Table\Generated\LogMessageTable::COLUMN_LOG_ID),
            'level' => $builder->fieldInteger(Table\Generated\LogMessageTable::COLUMN_LEVEL),
            'message' => Table\Generated\LogMessageTable::COLUMN_MESSAGE,
            'context' => $builder->fieldJson(Table\Generated\LogMessageTable::COLUMN_CONTEXT),
            'insertDate' => $builder->fieldDateTime(Table\Generated\LogMessageTable::COLUMN_INSERT_DATE),
        ]);

        return $builder->build($definition);
    }
}
