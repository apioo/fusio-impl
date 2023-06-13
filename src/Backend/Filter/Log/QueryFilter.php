<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Backend\Filter\Log;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View\QueryFilterAbstract;
use PSX\Sql\Condition;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to, ?int $routeId = null, ?int $appId = null, ?int $userId = null, ?string $ip = null, ?string $userAgent = null, ?string $method = null, ?string $path = null, ?string $header = null, ?string $body = null)
    {
        parent::__construct($from, $to);

        $this->routeId = $routeId;
        $this->appId = $appId;
        $this->userId = $userId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->method = $method;
        $this->path = $path;
        $this->header = $header;
        $this->body = $body;
    }

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

    public static function create(RequestInterface $request): self
    {
        [$from, $to] = self::getFromAndTo($request);

        $routeId   = self::toInt($request->get('routeId'));
        $appId     = self::toInt($request->get('appId'));
        $userId    = self::toInt($request->get('userId'));
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
                } elseif (str_starts_with($part, '/')) {
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

        return new self($from, $to, $routeId, $appId, $userId, $ip, $userAgent, $method, $path, $header, $body);
    }
}
