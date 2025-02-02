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

namespace Fusio\Impl\Backend\View\Statistic;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\Log;
use Fusio\Impl\Table;
use PSX\Sql\ViewAbstract;

/**
 * ErrorsPerOperation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ErrorsPerOperation extends ViewAbstract
{
    public function getView(Log\LogQueryFilter $filter, ContextInterface $context)
    {
        $condition = $filter->getCondition([], 'log');
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->notNil('log.' . Table\Generated\LogTable::COLUMN_OPERATION_ID);

        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used routes and build data structure
        $sql = '    SELECT log.operation_id,
                           operation.name
                      FROM fusio_log_error error
                INNER JOIN fusio_log log
                        ON log.id = error.log_id
                INNER JOIN fusio_operation operation
                        ON log.operation_id = operation.id
                     WHERE ' . $expression . '
                  GROUP BY log.operation_id, operation.name
                  ORDER BY COUNT(error.id) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());
        $operationIds = [];
        $data = [];
        $series = [];

        foreach ($result as $row) {
            $operationIds[] = $row['operation_id'];

            $data[$row['operation_id']] = [];
            $series[$row['operation_id']] = $row['name'];

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['operation_id']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($operationIds)) {
            $condition->in('log.operation_id', $operationIds);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(error.id) AS cnt,
                           log.operation_id,
                           DATE(log.date) AS date
                      FROM fusio_log_error error
                INNER JOIN fusio_log log
                        ON log.id = error.log_id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.operation_id';

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['operation_id']][$row['date']])) {
                $data[$row['operation_id']][$row['date']] = (int) $row['cnt'];
            }
        }

        // build labels
        $fromDate = $filter->getFrom();
        $toDate   = $filter->getTo();
        $diff     = $toDate->getTimestamp() - $fromDate->getTimestamp();
        $labels   = [];
        while ($fromDate <= $toDate) {
            $labels[] = $fromDate->format($diff < 2419200 ? 'D' : 'Y-m-d');

            $fromDate = $fromDate->add(new \DateInterval('P1D'));
        }

        // clean data structure
        $values = [];
        foreach ($data as $row) {
            $values[] = array_values($row);
        }

        return [
            'labels' => $labels,
            'data'   => $values,
            'series' => array_values($series),
        ];
    }
}
