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

use PSX\Sql\Condition;

/**
 * QueryFilterAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class QueryFilterAbstract
{
    /**
     * @var \DateTime
     */
    protected $from;

    /**
     * @var \DateTime
     */
    protected $to;

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getCondition($alias = null)
    {
        $alias     = $alias !== null ? $alias . '.' : '';
        $condition = new Condition();
        $condition->greaterThen($alias . 'date', $this->from->format('Y-m-d 00:00:00'));
        $condition->lowerThen($alias . 'date', $this->to->format('Y-m-d 23:59:59'));

        return $condition;
    }

    public static function create(array $parameters)
    {
        $from = new \DateTime(isset($parameters['from']) ? $parameters['from'] : '-1 month');
        $to   = new \DateTime(isset($parameters['to']) ? $parameters['to'] : 'now');

        // from date is large then to date
        if ($from->getTimestamp() > $to->getTimestamp()) {
            $tmp  = clone $from;
            $from = $to;
            $to   = $tmp;
        }

        // check if diff between from and to is larger then ca 2 months
        if (($to->getTimestamp() - $from->getTimestamp()) > 4838400) {
            $to = clone $from;
            $to->add(new \DateInterval('P2M'));
        }

        $filter = new static();
        $filter->from = $from;
        $filter->to   = $to;

        return $filter;
    }
}
