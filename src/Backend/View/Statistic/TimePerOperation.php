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

namespace Fusio\Impl\Backend\View\Statistic;

use Fusio\Impl\Backend\Filter\Log;
use PSX\Sql\ViewAbstract;

/**
 * TimePerOperation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TimePerOperation extends ViewAbstract
{
    public function getView(int $categoryId, Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most slowest routes and build data structure
        $sql = '    SELECT log.operation_id
                      FROM fusio_log log
                     WHERE log.category_id = ?
                       AND log.operation_id IS NOT NULL
                       AND log.execution_time IS NOT NULL
                       AND ' . $expression . '
                  GROUP BY log.operation_id
                  ORDER BY SUM(log.execution_time) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result   = $this->connection->fetchAllAssociative($sql, array_merge([$categoryId], $condition->getValues()));
        $routeIds = array();
        $data     = [];
        $series   = [];

        foreach ($result as $row) {
            $routeIds[] = $row['operation_id'];

            $data[$row['operation_id']] = [];
            $series[$row['operation_id']] = null;

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['operation_id']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($routeIds)) {
            $condition->in('log.operation_id', $routeIds);
        }

        $condition->notNil('log.execution_time');

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT AVG(log.execution_time / 1000) AS exec_time,
                           log.operation_id,
                           operation.name,
                           DATE(log.date) AS date
                      FROM fusio_log log
                INNER JOIN fusio_operation operation
                        ON log.operation_id = operation.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.operation_id, operation.name';

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['operation_id']][$row['date']])) {
                $series[$row['operation_id']] = $row['name'] . ' (ms)';
                $data[$row['operation_id']][$row['date']] = (float) $row['exec_time']; // microseconds
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
