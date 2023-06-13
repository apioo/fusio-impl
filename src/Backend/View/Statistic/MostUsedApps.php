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
 * MostUsedApps
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MostUsedApps extends ViewAbstract
{
    public function getView(Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used apps and build data structure
        $sql = '  SELECT log.app_id
                    FROM fusio_log log
                   WHERE ' . $expression . '
                     AND log.app_id IS NOT NULL
                GROUP BY log.app_id
                ORDER BY COUNT(log.app_id) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());
        $appIds = array();
        $data   = [];
        $series = [];

        foreach ($result as $row) {
            $appIds[] = $row['app_id'];

            $data[$row['app_id']] = [];
            $series[$row['app_id']] = null;

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['app_id']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($appIds)) {
            $condition->in('log.app_id', $appIds);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(log.id) AS cnt,
                           log.app_id,
                           app.name,
                           DATE(log.date) AS date
                      FROM fusio_log log
                INNER JOIN fusio_app app
                        ON log.app_id = app.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.app_id, app.name';

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['app_id']][$row['date']])) {
                $series[$row['app_id']] = $row['name'];
                $data[$row['app_id']][$row['date']] = (int) $row['cnt'];
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
