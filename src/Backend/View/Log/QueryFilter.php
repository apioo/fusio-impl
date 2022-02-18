<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View\QueryFilterAbstract;
use PSX\Sql\Condition;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class QueryFilter extends QueryFilterAbstract
{
    protected ?int $routeId = null;
    protected ?int $appId = null;
    protected ?int $userId = null;
    protected ?string $ip = null;
    protected ?string $userAgent = null;
    protected ?string $method = null;
    protected ?string $path = null;
    protected ?string $header = null;
    protected ?string $body = null;

    public function getRouteId(): ?int
    {
        return $this->routeId;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getCondition(?string $alias = null): Condition
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

    public static function create(RequestInterface $request): static
    {
        $filter    = parent::create($request);
        $routeId   = $request->get('routeId');
        $appId     = $request->get('appId');
        $userId    = $request->get('userId');
        $ip        = $request->get('ip');
        $userAgent = $request->get('userAgent');
        $method    = $request->get('method');
        $path      = $request->get('path');
        $header    = $request->get('header');
        $body      = $request->get('body');
        $search    = $request->get('search');

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

        if ($filter instanceof self) {
            $filter->routeId   = $routeId;
            $filter->appId     = $appId;
            $filter->userId    = $userId;
            $filter->ip        = $ip;
            $filter->userAgent = $userAgent;
            $filter->method    = $method;
            $filter->path      = $path;
            $filter->header    = $header;
            $filter->body      = $body;
        }

        return $filter;
    }
}
