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
 * ErrorsPerRoute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ErrorsPerRoute extends ViewAbstract
{
    public function getView(int $categoryId, Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used routes and build data structure
        $sql = '    SELECT log.route_id
                      FROM fusio_log_error error
                INNER JOIN fusio_log log
                        ON log.id = error.log_id
                     WHERE log.category_id = :category_id
                       AND log.route_id IS NOT NULL
                       AND ' . $expression . '
                  GROUP BY log.route_id
                  ORDER BY COUNT(error.id) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result   = $this->connection->fetchAll($sql, array_merge(['category_id' => $categoryId], $condition->getValues()));
        $routeIds = array();
        $data     = [];
        $series   = [];

        foreach ($result as $row) {
            $routeIds[] = $row['route_id'];

            $data[$row['route_id']] = [];
            $series[$row['route_id']] = null;

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['route_id']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($routeIds)) {
            $condition->in('log.route_id', $routeIds);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(error.id) AS cnt,
                           log.route_id,
                           routes.path,
                           DATE(log.date) AS date
                      FROM fusio_log_error error
                INNER JOIN fusio_log log
                        ON log.id = error.log_id
                INNER JOIN fusio_routes routes
                        ON log.route_id = routes.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.route_id, routes.path';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['route_id']][$row['date']])) {
                $series[$row['route_id']] = $row['path'];
                $data[$row['route_id']][$row['date']] = (int) $row['cnt'];
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
            'data'   => array_values($values),
            'series' => array_values($series),
        ];
    }
}
