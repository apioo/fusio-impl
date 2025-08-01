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

namespace Fusio\Impl\Backend\View\Statistic;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\Log;
use Fusio\Impl\Table;
use Fusio\Model\Backend\StatisticChart;

/**
 * MostUsedApps
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MostUsedApps extends ChartViewAbstract
{
    public function getView(Log\LogQueryFilter $filter, ContextInterface $context): StatisticChart
    {
        $condition = $filter->getCondition([], 'log');
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->notNil('log.' . Table\Generated\LogTable::COLUMN_APP_ID);

        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used apps and build data structure
        $sql = '  SELECT log.app_id,
                         app.name
                    FROM fusio_log log
              INNER JOIN fusio_app app
                      ON log.app_id = app.id
                   WHERE ' . $expression . '
                GROUP BY log.app_id, app.name
                ORDER BY COUNT(log.app_id) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());
        $appIds = array();
        $data   = [];
        $series = [];

        foreach ($result as $row) {
            $appIds[] = $row['app_id'];

            $data[$row['app_id']] = [];
            $series[$row['app_id']] = $row['name'];

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
                           DATE(log.date) AS date
                      FROM fusio_log log
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.app_id';

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['app_id']][$row['date']])) {
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

        return $this->build($data, $series, $labels);
    }
}
