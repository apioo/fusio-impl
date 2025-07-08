<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use PSX\Sql\Condition;

/**
 * LogQueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LogQueryFilter extends DateQueryFilter
{
    private ?int $operationId = null;
    private ?int $appId = null;
    private ?int $userId = null;
    private ?string $ip = null;
    private ?string $userAgent = null;
    private ?string $method = null;
    private ?string $path = null;
    private ?string $header = null;
    private ?string $body = null;

    public function __construct(?int $operationId, ?int $appId, ?int $userId, ?string $ip, ?string $userAgent, ?string $method, ?string $path, ?string $header, ?string $body, \DateTimeImmutable $from, \DateTimeImmutable $to, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        parent::__construct($from, $to, $startIndex, $count, $search, $sortBy, $sortOrder);

        $this->operationId = $operationId;
        $this->appId = $appId;
        $this->userId = $userId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->method = $method;
        $this->path = $path;
        $this->header = $header;
        $this->body = $body;
    }

    public function getOperationId(): ?int
    {
        return $this->operationId;
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

    public function getCondition(array $columnMapping, ?string $alias = null): Condition
    {
        $condition = parent::getCondition($columnMapping, $alias);
        $alias = $this->getAlias($alias);

        if (!empty($this->operationId)) {
            $condition->equals($alias . 'operation_id', $this->operationId);
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

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        $arguments = parent::getConstructorArguments($request);

        $operationId = self::toInt($request->get('operationId'));
        $appId = self::toInt($request->get('appId'));
        $userId = self::toInt($request->get('userId'));
        $ip = $request->get('ip');
        $userAgent = $request->get('userAgent');
        $method = $request->get('method');
        $path = $request->get('path');
        $header = $request->get('header');
        $body = $request->get('body');

        $search = $arguments['search'] ?? null;
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

            $arguments['search'] = null;
        }

        $arguments['operationId'] = $operationId;
        $arguments['appId'] = $appId;
        $arguments['userId'] = $userId;
        $arguments['ip'] = $ip;
        $arguments['userAgent'] = $userAgent;
        $arguments['method'] = $method;
        $arguments['path'] = $path;
        $arguments['header'] = $header;
        $arguments['body'] = $body;

        return $arguments;
    }
}
