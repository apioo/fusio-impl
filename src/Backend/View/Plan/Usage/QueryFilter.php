<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\View\Plan\Usage;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View\QueryFilterAbstract;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class QueryFilter extends QueryFilterAbstract
{
    /**
     * @var integer
     */
    protected $routeId;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var integer
     */
    protected $appId;

    public function getRouteId()
    {
        return $this->routeId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getCondition($alias = null)
    {
        $condition = parent::getCondition($alias);
        $alias     = $alias !== null ? $alias . '.' : '';

        if (!empty($this->routeId)) {
            $condition->equals($alias . 'route_id', $this->routeId);
        }

        if (!empty($this->userId)) {
            $condition->equals($alias . 'user_id', $this->userId);
        }

        if (!empty($this->appId)) {
            $condition->equals($alias . 'app_id', $this->appId);
        }

        return $condition;
    }

    protected function getDateColumn()
    {
        return 'insert_date';
    }

    public static function create(RequestInterface $request)
    {
        $filter  = parent::create($request);
        $routeId = $request->get('routeId');
        $userId  = $request->get('userId');
        $appId   = $request->get('appId');
        $search  = $request->get('search');

        // parse search if available
        if (!empty($search)) {
            $parts = explode(',', $search);
            foreach ($parts as $part) {
                $part = trim($part);
            }
        }

        $filter->routeId = $routeId;
        $filter->userId  = $userId;
        $filter->appId   = $appId;

        return $filter;
    }
}
