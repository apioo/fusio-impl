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
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Error
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Error extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\LogErrorTable::COLUMN_MESSAGE], 'error');
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'error.' . Table\Generated\LogErrorTable::COLUMN_ID,
                'error.' . Table\Generated\LogErrorTable::COLUMN_LOG_ID,
                'error.' . Table\Generated\LogErrorTable::COLUMN_MESSAGE,
                'error.' . Table\Generated\LogErrorTable::COLUMN_FILE,
                'error.' . Table\Generated\LogErrorTable::COLUMN_LINE,
                'error.' . Table\Generated\LogErrorTable::COLUMN_INSERT_DATE,
            ])
            ->from('fusio_log_error', 'error')
            ->innerJoin('error', 'fusio_log', 'log', 'error.' . Table\Generated\LogErrorTable::COLUMN_LOG_ID . ' = log.' . Table\Generated\LogTable::COLUMN_ID)
            ->orderBy('error.' . Table\Generated\LogErrorTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_log_error', 'error')
            ->innerJoin('error', 'fusio_log', 'log', 'error.' . Table\Generated\LogErrorTable::COLUMN_LOG_ID . ' = log.' . Table\Generated\LogTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\LogErrorTable::COLUMN_ID),
                'logId' => $builder->fieldInteger(Table\Generated\LogErrorTable::COLUMN_LOG_ID),
                'message' => Table\Generated\LogErrorTable::COLUMN_MESSAGE,
                'file' => Table\Generated\LogErrorTable::COLUMN_FILE,
                'line' => Table\Generated\LogErrorTable::COLUMN_LINE,
                'insertDate' => $builder->fieldDateTime(Table\Generated\LogErrorTable::COLUMN_INSERT_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals('error.' . Table\Generated\LogErrorTable::COLUMN_ID, $id);
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'error.' . Table\Generated\LogErrorTable::COLUMN_ID,
                'error.' . Table\Generated\LogErrorTable::COLUMN_LOG_ID,
                'error.' . Table\Generated\LogErrorTable::COLUMN_MESSAGE,
                'error.' . Table\Generated\LogErrorTable::COLUMN_TRACE,
                'error.' . Table\Generated\LogErrorTable::COLUMN_FILE,
                'error.' . Table\Generated\LogErrorTable::COLUMN_LINE,
                'error.' . Table\Generated\LogErrorTable::COLUMN_INSERT_DATE,
            ])
            ->from('fusio_log_error', 'error')
            ->innerJoin('error', 'fusio_log', 'log', 'error.' . Table\Generated\LogErrorTable::COLUMN_LOG_ID . ' = log.' . Table\Generated\LogTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
            'id' => $builder->fieldInteger(Table\Generated\LogErrorTable::COLUMN_ID),
            'logId' => $builder->fieldInteger(Table\Generated\LogErrorTable::COLUMN_LOG_ID),
            'message' => Table\Generated\LogErrorTable::COLUMN_MESSAGE,
            'trace' => Table\Generated\LogErrorTable::COLUMN_TRACE,
            'file' => Table\Generated\LogErrorTable::COLUMN_FILE,
            'line' => Table\Generated\LogErrorTable::COLUMN_LINE,
            'insertDate' => $builder->fieldDateTime(Table\Generated\LogErrorTable::COLUMN_INSERT_DATE),
        ]);

        return $builder->build($definition);
    }
}
