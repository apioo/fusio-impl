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
 * ActivitiesPerUser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActivitiesPerUser extends ViewAbstract
{
    public function getView(AuditQueryFilter $filter, ContextInterface $context)
    {
        $condition = $filter->getCondition([], 'audit');
        $condition->equals('audit.' . Table\Generated\AuditTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->notNil('audit.' . Table\Generated\AuditTable::COLUMN_EVENT);

        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used routes and build data structure
        $sql = '    SELECT audit.user_id,
                           usr.name
                      FROM fusio_audit audit
                INNER JOIN fusio_user usr
                        ON audit.user_id = usr.id
                     WHERE ' . $expression . '
                  GROUP BY audit.user_id, usr.name
                  ORDER BY COUNT(audit.id) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());
        $userIds = [];
        $data = [];
        $series = [];

        foreach ($result as $row) {
            $userIds[] = $row['user_id'];

            $data[$row['user_id']] = [];
            $series[$row['user_id']] = $row['name'];

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['user_id']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($userIds)) {
            $condition->in('audit.user_id', $userIds);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(audit.id) AS cnt,
                           audit.user_id,
                           DATE(audit.date) AS date
                      FROM fusio_audit audit
                     WHERE ' . $expression . '
                  GROUP BY DATE(audit.date), audit.user_id';

        $result = $this->connection->fetchAllAssociative($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['user_id']][$row['date']])) {
                $data[$row['user_id']][$row['date']] = (int) $row['cnt'];
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
