<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
        $sql = '    SELECT log.routeId
                      FROM fusio_log_error error
                INNER JOIN fusio_log log
                        ON log.id = error.logId
                     WHERE ' . $expression . '
                       AND log.routeId IS NOT NULL
                  GROUP BY log.routeId
                  ORDER BY COUNT(error.id) DESC
                     LIMIT 6';

        $result   = $this->connection->fetchAll($sql, $condition->getValues());
        $routeIds = array();
        $data     = [];
        $series   = [];

        foreach ($result as $row) {
            $routeIds[] = $row['routeId'];

            $data[$row['routeId']] = [];
            $series[$row['routeId']] = null;

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['routeId']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($routeIds)) {
            $condition->in('log.routeId', $routeIds);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(error.id) AS cnt,
                           log.routeId,
                           routes.path,
                           DATE(log.date) AS date
                      FROM fusio_log_error error
                INNER JOIN fusio_log log
                        ON log.id = error.logId
                INNER JOIN fusio_routes routes
                        ON log.routeId = routes.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.routeId';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['routeId']][$row['date']])) {
                $series[$row['routeId']] = $row['path'];
                $data[$row['routeId']][$row['date']] = (int) $row['cnt'];
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
        $sql = '  SELECT log.appId
                    FROM fusio_log log
                   WHERE ' . $expression . '
                     AND log.appId IS NOT NULL
                GROUP BY log.appId
                ORDER BY COUNT(log.appId) DESC
                   LIMIT 6';

        $result = $this->connection->fetchAll($sql, $condition->getValues());
        $appIds = array();
        $data   = [];
        $series = [];

        foreach ($result as $row) {
            $appIds[] = $row['appId'];

            $data[$row['appId']] = [];
            $series[$row['appId']] = null;

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['appId']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($appIds)) {
            $condition->in('log.appId', $appIds);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(log.id) AS cnt,
                           log.appId,
                           app.name,
                           DATE(log.date) AS date
                      FROM fusio_log log
                INNER JOIN fusio_app app
                        ON log.appId = app.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.appId';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['appId']][$row['date']])) {
                $series[$row['appId']] = $row['name'];
                $data[$row['appId']][$row['date']] = (int) $row['cnt'];
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
        $sql = '  SELECT log.routeId
                    FROM fusio_log log
                   WHERE ' . $expression . '
                     AND log.routeId IS NOT NULL
                GROUP BY log.routeId
                ORDER BY COUNT(log.routeId) DESC
                   LIMIT 6';

        $result   = $this->connection->fetchAll($sql, $condition->getValues());
        $routeIds = array();
        $data     = [];
        $series   = [];

        foreach ($result as $row) {
            $routeIds[] = $row['routeId'];

            $data[$row['routeId']] = [];
            $series[$row['routeId']] = null;

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['routeId']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($routeIds)) {
            $condition->in('log.routeId', $routeIds);
        }

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT COUNT(log.id) AS cnt,
                           log.routeId,
                           routes.path,
                           DATE(log.date) AS date
                      FROM fusio_log log
                INNER JOIN fusio_routes routes
                        ON log.routeId = routes.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.routeId';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['routeId']][$row['date']])) {
                $series[$row['routeId']] = $row['path'];
                $data[$row['routeId']][$row['date']] = (int) $row['cnt'];
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
        $sql = '  SELECT AVG(log.executionTime / 1000) AS execTime,
                         DATE(log.date) AS date
                    FROM fusio_log log
                   WHERE ' . $expression . '
                GROUP BY DATE(log.date)';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['date']])) {
                $data[$row['date']] = (float) $row['execTime']; // microseconds
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
        $sql = '    SELECT log.routeId
                      FROM fusio_log log
                     WHERE ' . $expression . '
                       AND log.routeId IS NOT NULL
                       AND log.executionTime IS NOT NULL
                  GROUP BY log.routeId
                  ORDER BY SUM(log.executionTime) DESC
                     LIMIT 6';

        $result   = $this->connection->fetchAll($sql, $condition->getValues());
        $routeIds = array();
        $data     = [];
        $series   = [];

        foreach ($result as $row) {
            $routeIds[] = $row['routeId'];

            $data[$row['routeId']] = [];
            $series[$row['routeId']] = null;

            $fromDate = $filter->getFrom();
            $toDate   = $filter->getTo();
            while ($fromDate <= $toDate) {
                $data[$row['routeId']][$fromDate->format('Y-m-d')] = 0;

                $fromDate = $fromDate->add(new \DateInterval('P1D'));
            }
        }

        if (!empty($routeIds)) {
            $condition->in('log.routeId', $routeIds);
        }

        $condition->notNil('log.executionTime');

        // fill data with values
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = '    SELECT AVG(log.executionTime / 1000) AS execTime,
                           log.routeId,
                           routes.path,
                           DATE(log.date) AS date
                      FROM fusio_log log
                INNER JOIN fusio_routes routes
                        ON log.routeId = routes.id
                     WHERE ' . $expression . '
                  GROUP BY DATE(log.date), log.routeId';

        $result = $this->connection->fetchAll($sql, $condition->getValues());

        foreach ($result as $row) {
            if (isset($data[$row['routeId']][$row['date']])) {
                $series[$row['routeId']] = $row['path'] . ' (ms)';
                $data[$row['routeId']][$row['date']] = (float) $row['execTime']; // microseconds
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
