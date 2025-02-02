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
use Fusio\Impl\Backend\Filter\Audit\AuditQueryFilter;
use Fusio\Impl\Table;
use PSX\Sql\ViewAbstract;

/**
 * MostUsedActivities
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MostUsedActivities extends ViewAbstract
{
    public function getView(AuditQueryFilter $filter, ContextInterface $context)
    {
        $condition = $filter->getCondition([], 'audit');
        $condition->equals('audit.' . Table\Generated\AuditTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->notNil('audit.' . Table\Generated\AuditTable::COLUMN_EVENT);

        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used events and build data structure
        $sql = '  SELECT audit.event
                    FROM fusio_audit audit
                   WHERE ' . $expression . '
                GROUP BY audit.event
                ORDER BY COUNT(audit.event) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());
        $events = [];
        $data   = [];
        $series = [];

        foreach ($result as $row) {
            $events[] = $row['event'];

            $data[$row['event']] = [];
            $series[$row['event']] = $row['event'];

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['event']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($events)) {
            $condition->in('audit.event', $events);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(audit.id) AS cnt,
                           audit.event,
                           DATE(audit.date) AS date
                      FROM fusio_audit audit
                     WHERE ' . $expression . '
                  GROUP BY DATE(audit.date), audit.event';

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['event']][$row['date']])) {
                $data[$row['event']][$row['date']] = (int) $row['cnt'];
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
