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
 * CountRequests
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CountRequests extends ViewAbstract
{
    public function getView(Log\QueryFilter $filter)
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
}
