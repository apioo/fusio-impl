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

namespace Fusio\Impl\Backend\View\Log;

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
    protected $routeId;

    /**
     * @var integer
     */
    protected $appId;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $header;

    /**
     * @var string
     */
    protected $body;

    public function getRouteId()
    {
        return $this->routeId;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getCondition($alias = null)
    {
        $condition = parent::getCondition($alias);
        $alias     = $alias !== null ? $alias . '.' : '';

        if (!empty($this->routeId)) {
            $condition->equals($alias . 'route_id', $this->routeId);
        }

        if (!empty($this->appId)) {
            $condition->equals($alias . 'app_id', $this->appId);
        }

        if (!empty($this->userId)) {
            $condition->equals($alias . 'user_id', $this->userId);
        }

        if (!empty($this->ip)) {
            $condition->like($alias . 'ip', $this->ip);
        }

        if (!empty($this->userAgent)) {
            $condition->like($alias . 'user_agent', '%' . $this->userAgent . '%');
        }

        if (!empty($this->method)) {
            $condition->equals($alias . 'method', $this->method);
        }

        if (!empty($this->path)) {
            $condition->like($alias . 'path', $this->path . '%');
        }

        if (!empty($this->header)) {
            $condition->like($alias . 'header', '%' . $this->header . '%');
        }

        if (!empty($this->body)) {
            $condition->like($alias . 'body', '%' . $this->body . '%');
        }

        return $condition;
    }

    public static function create(array $parameters)
    {
        $filter    = parent::create($parameters);
        $routeId   = isset($parameters['routeId']) ? $parameters['routeId'] : null;
        $appId     = isset($parameters['appId']) ? $parameters['appId'] : null;
        $userId    = isset($parameters['userId']) ? $parameters['userId'] : null;
        $ip        = isset($parameters['ip']) ? $parameters['ip'] : null;
        $userAgent = isset($parameters['userAgent']) ? $parameters['userAgent'] : null;
        $method    = isset($parameters['method']) ? $parameters['method'] : null;
        $path      = isset($parameters['path']) ? $parameters['path'] : null;
        $header    = isset($parameters['header']) ? $parameters['header'] : null;
        $body      = isset($parameters['body']) ? $parameters['body'] : null;
        $search    = isset($parameters['search']) ? $parameters['search'] : null;

        // parse search if available
        if (!empty($search)) {
            $parts = explode(',', $search);
            foreach ($parts as $part) {
                $part = trim($part);
                if (filter_var($part, FILTER_VALIDATE_IP) !== false) {
                    $ip = $part;
                } elseif (substr($part, 0, 1) == '/') {
                    $path = $part;
                } elseif (in_array($part, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                    $method = $part;
                } elseif (preg_match('/^([A-z\-]+): (.*)$/', $part)) {
                    $header = $part;
                } else {
                    $body = $part;
                }
            }
        }

        $filter->routeId   = $routeId;
        $filter->appId     = $appId;
        $filter->userId    = $userId;
        $filter->ip        = $ip;
        $filter->userAgent = $userAgent;
        $filter->method    = $method;
        $filter->path      = $path;
        $filter->header    = $header;
        $filter->body      = $body;

        return $filter;
    }
}
