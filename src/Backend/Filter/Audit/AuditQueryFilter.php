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

namespace Fusio\Impl\Backend\Filter\Audit;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use PSX\Sql\Condition;

/**
 * AuditQueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuditQueryFilter extends DateQueryFilter
{
    private ?int $appId = null;
    private ?int $userId = null;
    private ?string $event = null;
    private ?string $ip = null;
    private ?string $message = null;

    public function __construct(?int $appId, ?int $userId, ?string $event, ?string $ip, ?string $message, \DateTimeImmutable $from, \DateTimeImmutable $to, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        parent::__construct($from, $to, $startIndex, $count, $search, $sortBy, $sortOrder);

        $this->appId = $appId;
        $this->userId = $userId;
        $this->event = $event;
        $this->ip = $ip;
        $this->message = $message;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getCondition(array $columnMapping, ?string $alias = null): Condition
    {
        $condition = parent::getCondition($columnMapping, $alias);
        $alias = $this->getAlias($alias);

        if (!empty($this->appId)) {
            $condition->equals($alias . 'app_id', $this->appId);
        }

        if (!empty($this->userId)) {
            $condition->equals($alias . 'user_id', $this->userId);
        }

        if (!empty($this->event)) {
            $condition->like($alias . 'event', '%' . $this->event . '%');
        }

        if (!empty($this->ip)) {
            $condition->like($alias . 'ip', $this->ip);
        }

        if (!empty($this->message)) {
            $condition->like($alias . 'message', '%' . $this->message . '%');
        }

        return $condition;
    }

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        $arguments = parent::getConstructorArguments($request);

        $appId = self::toInt($request->get('appId'));
        $userId = self::toInt($request->get('userId'));
        $event = $request->get('event');
        $ip = $request->get('ip');
        $message = $request->get('message');

        $search = $arguments['search'] ?? null;
        if (!empty($search)) {
            $parts = explode(',', $search);
            foreach ($parts as $part) {
                $part = trim($part);
                if (filter_var($part, FILTER_VALIDATE_IP) !== false) {
                    $ip = $part;
                } else {
                    $message = $search;
                }
            }

            $arguments['search'] = null;
        }

        $arguments['appId'] = $appId;
        $arguments['userId'] = $userId;
        $arguments['event'] = $event;
        $arguments['ip'] = $ip;
        $arguments['message'] = $message;

        return $arguments;
    }
}
