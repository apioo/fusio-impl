<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\View;

use PSX\Sql\ViewAbstract;

/**
 * Statistic
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Statistic extends ViewAbstract
{
    public function getErrorsPerRoute(Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used routes and build data structure
        $sql = '    SELECT log.route_id
                      FROM fusio_log_error error
                INNER JOIN fusio_log log
                        ON log.id = error.log_id
                     WHERE ' . $expression . '
                       AND log.route_id IS NOT NULL
                  GROUP BY log.route_id
                  ORDER BY COUNT(error.id) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result   = $this->connection->fetchAll($sql, $condition->getValues());
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

    public function getIncomingRequests(Log\QueryFilter $filter)
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
                   WHERE ' . $expression . '
                GROUP BY DATE(log.date)';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

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

    public function getMostUsedApps(Log\QueryFilter $filter)
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

        $result = $this->connection->fetchAll($sql, $condition->getValues());
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

        $result = $this->connection->fetchAll($sql, $condition->getValues());

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
            'data'   => array_values($values),
            'series' => array_values($series),
        ];
    }

    public function getMostUsedRoutes(Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most used routes and build data structure
        $sql = '  SELECT log.route_id
                    FROM fusio_log log
                   WHERE ' . $expression . '
                     AND log.route_id IS NOT NULL
                GROUP BY log.route_id
                ORDER BY COUNT(log.route_id) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result   = $this->connection->fetchAll($sql, $condition->getValues());
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

        $sql = '    SELECT COUNT(log.id) AS cnt,
                           log.route_id,
                           routes.path,
                           DATE(log.date) AS date
                      FROM fusio_log log
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

    public function getIssuedTokens(App\Token\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('token');
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
        $sql = '  SELECT COUNT(token.id) AS cnt,
                         DATE(token.date) AS date
                    FROM fusio_app_token token
                   WHERE ' . $expression . '
                GROUP BY DATE(token.date)';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['date']])) {
                $data[$row['date']] = (int) $row['cnt'];
            }
        }

        return [
            'labels' => $labels,
            'data'   => [array_values($data)],
            'series' => ['Tokens'],
        ];
    }

    public function getCountRequests(Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = 'SELECT COUNT(log.id) AS cnt
                  FROM fusio_log log
                 WHERE ' . $expression;

        $row = $this->connection->fetchAssoc($sql, $condition->getValues());

        return [
            'count' => (int) $row['cnt'],
            'from'  => $filter->getFrom()->format(\DateTime::RFC3339),
            'to'    => $filter->getTo()->format(\DateTime::RFC3339),
        ];
    }

    public function getTimeAverage(Log\QueryFilter $filter)
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
                   WHERE ' . $expression . '
                GROUP BY DATE(log.date)';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

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

    public function getTimePerRoute(Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        // get the most slowest routes and build data structure
        $sql = '    SELECT log.route_id
                      FROM fusio_log log
                     WHERE ' . $expression . '
                       AND log.route_id IS NOT NULL
                       AND log.execution_time IS NOT NULL
                  GROUP BY log.route_id
                  ORDER BY SUM(log.execution_time) DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $result   = $this->connection->fetchAll($sql, $condition->getValues());
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

        $condition->notNil('log.execution_time');

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT AVG(log.execution_time / 1000) AS exec_time,
                           log.route_id,
                           routes.path,
                           DATE(log.date) AS date
                      FROM fusio_log log
                INNER JOIN fusio_routes routes
                        ON log.route_id = routes.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.route_id, routes.path';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['route_id']][$row['date']])) {
                $series[$row['route_id']] = $row['path'] . ' (ms)';
                $data[$row['route_id']][$row['date']] = (float) $row['exec_time']; // microseconds
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
