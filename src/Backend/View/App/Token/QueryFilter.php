<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\View\App\Token;

use PSX\Sql\Condition;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class QueryFilter
{
    /**
     * @var \DateTime
     */
    protected $from;

    /**
     * @var \DateTime
     */
    protected $to;

    /**
     * @var integer
     */
    protected $appId;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var integer
     */
    protected $status;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $ip;

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getCondition($alias = null)
    {
        $alias     = $alias !== null ? $alias . '.' : '';
        $condition = new Condition();
        $condition->greaterThen($alias . 'date', $this->from->format('Y-m-d 00:00:00'));
        $condition->lowerThen($alias . 'date', $this->to->format('Y-m-d 23:59:59'));

        if (!empty($this->appId)) {
            $condition->equals($alias . 'appId', $this->appId);
        }

        if (!empty($this->userId)) {
            $condition->equals($alias . 'userId', $this->userId);
        }

        if (!empty($this->status)) {
            $condition->equals($alias . 'status', $this->status);
        }

        if (!empty($this->scope)) {
            $condition->like($alias . 'scope', '%' . $this->scope . '%');
        }

        if (!empty($this->ip)) {
            $condition->like($alias . 'ip', $this->ip);
        }

        return $condition;
    }

    public static function create(array $parameters)
    {
        $from   = isset($parameters['from']) ? $parameters['from'] : '-1 month';
        $to     = isset($parameters['to']) ? $parameters['to'] : 'now';
        $appId  = isset($parameters['appId']) ? $parameters['appId'] : null;
        $userId = isset($parameters['userId']) ? $parameters['userId'] : null;
        $status = isset($parameters['status']) ? $parameters['status'] : null;
        $scope  = isset($parameters['scope']) ? $parameters['scope'] : null;
        $ip     = isset($parameters['ip']) ? $parameters['ip'] : null;
        $search = isset($parameters['search']) ? $parameters['search'] : null;

        $from = new \DateTime($from);
        $to   = new \DateTime($to);

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

        // parse search if available
        if (!empty($search)) {
            $parts = explode(',', $search);
            foreach ($parts as $part) {
                $part = trim($part);
                if (filter_var($part, FILTER_VALIDATE_IP) !== false) {
                    $ip = $part;
                } else {
                    $scope = $part;
                }
            }
        }

        $filter = new self();
        $filter->from   = $from;
        $filter->to     = $to;
        $filter->appId  = $appId;
        $filter->userId = $userId;
        $filter->status = $status;
        $filter->scope  = $scope;
        $filter->ip     = $ip;

        return $filter;
    }
}
