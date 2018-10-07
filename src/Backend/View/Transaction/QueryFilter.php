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

namespace Fusio\Impl\Backend\View\Transaction;

use Fusio\Impl\Backend\View\QueryFilterAbstract;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class QueryFilter extends QueryFilterAbstract
{
    /**
     * @var integer
     */
    protected $planId;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var integer
     */
    protected $appId;

    /**
     * @var integer
     */
    protected $status;

    /**
     * @var string
     */
    protected $provider;

    public function getPlanId()
    {
        return $this->planId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getCondition($alias = null)
    {
        $condition = parent::getCondition($alias);
        $alias     = $alias !== null ? $alias . '.' : '';

        if (!empty($this->planId)) {
            $condition->equals($alias . 'plan_id', $this->planId);
        }

        if (!empty($this->userId)) {
            $condition->equals($alias . 'user_id', $this->userId);
        }

        if (!empty($this->appId)) {
            $condition->equals($alias . 'app_id', $this->appId);
        }

        if (!empty($this->status)) {
            $condition->equals($alias . 'status', $this->status);
        }

        if (!empty($this->provider)) {
            $condition->like($alias . 'provider', $this->provider);
        }

        return $condition;
    }

    protected function getDateColumn()
    {
        return 'insert_date';
    }

    public static function create(array $parameters)
    {
        $filter   = parent::create($parameters);
        $planId   = isset($parameters['planId']) ? $parameters['planId'] : null;
        $userId   = isset($parameters['userId']) ? $parameters['userId'] : null;
        $appId    = isset($parameters['appId']) ? $parameters['appId'] : null;
        $status   = isset($parameters['status']) ? $parameters['status'] : null;
        $provider = isset($parameters['provider']) ? $parameters['provider'] : null;
        $search   = isset($parameters['search']) ? $parameters['search'] : null;

        // parse search if available
        if (!empty($search)) {
            $parts = explode(',', $search);
            foreach ($parts as $part) {
                $part = trim($part);
                if (is_numeric($part)) {
                    $status = intval($part);
                } else {
                    $provider = $part;
                }
            }
        }

        $filter->planId   = $planId;
        $filter->userId   = $userId;
        $filter->appId    = $appId;
        $filter->status   = $status;
        $filter->provider = $provider;

        return $filter;
    }
}
