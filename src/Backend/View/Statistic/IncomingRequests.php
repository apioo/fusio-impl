<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\View\Statistic;

use Fusio\Impl\Backend\View\Log;
use PSX\Sql\ViewAbstract;

/**
 * IncomingRequests
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class IncomingRequests extends ViewAbstract
{
    public function getView(int $categoryId, Log\QueryFilter $filter)
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
        $sql = '  SELECT COUNT(log.id) AS cnt,
                         DATE(log.date) AS date
                    FROM fusio_log log
                   WHERE log.category_id = :category_id
                     AND ' . $expression . '
                GROUP BY DATE(log.date)';

        $result = $this->connection->fetchAll($sql, array_merge(['category_id' => $categoryId], $condition->getValues()));

        foreach ($result as $row) {
            if (isset($data[$row['date']])) {
                $data[$row['date']] = (int) $row['cnt'];
            }
        }

        return [
            'labels' => $labels,
            'data'   => [array_values($data)],
            'series' => ['Requests'],
        ];
    }
}
