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
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\StatisticChart;

/**
 * UserRegistrations
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserRegistrations extends ChartViewAbstract
{
    public function getView(DateQueryFilter $filter, ContextInterface $context): StatisticChart
    {
        $condition  = $filter->getCondition([], 'usr');
        $condition->equals('usr.' . Table\Generated\UserTable::COLUMN_TENANT_ID, $context->getTenantId());

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
        $sql = '  SELECT COUNT(usr.id) AS cnt,
                         DATE(usr.date) AS date
                    FROM fusio_user usr
                   WHERE ' . $expression . '
                GROUP BY DATE(usr.date)';

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['date']])) {
                $data[$row['date']] = (int) $row['cnt'];
            }
        }

        return $this->build([$data], ['Users'], $labels);
    }
}
