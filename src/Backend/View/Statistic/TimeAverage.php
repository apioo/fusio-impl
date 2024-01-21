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
 * TimeAverage
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TimeAverage extends ViewAbstract
{
    public function getView(int $categoryId, Log\QueryFilter $filter, ?string $tenantId = null)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // build data structure
        $fromDate = $filter->getFrom();
        $toDate   = $filter->getTo();
        $diff     = $toDate->getTimestamp() - $fromDate->getTimestamp();
        $data     = [];
        $labels   = [];

        while ($fromDate <= $toDate) {
            $data[$fromDate->format('Y-m-d')] = 0;
            $labels[] = $fromDate->format($diff < 2419200 ? 'D' : 'Y-m-d');

            $fromDate = $fromDate->add(new \DateInterval('P1D'));
        }

        // fill values
        $sql = '  SELECT AVG(log.execution_time / 1000) AS exec_time,
                         DATE(log.date) AS date
                    FROM fusio_log log
                   WHERE log.category_id = ?
                     AND ' . $expression . '
                GROUP BY DATE(log.date)';

        $result = $this->connection->fetchAllAssociative($sql, array_merge([$categoryId], $condition->getValues()));

        foreach ($result as $row) {
            if (isset($data[$row['date']])) {
                $data[$row['date']] = (float) $row['exec_time']; // microseconds
            }
        }

        return [
            'labels' => $labels,
            'data'   => [array_values($data)],
            'series' => ['Execution time (ms)'],
        ];
    }
}
