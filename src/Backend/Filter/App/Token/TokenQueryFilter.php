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

namespace Fusio\Impl\Backend\Filter\App\Token;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use PSX\Sql\Condition;

/**
 * TokenQueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenQueryFilter extends DateQueryFilter
{
    private ?int $appId = null;
    private ?int $userId = null;
    private ?int $status = null;
    private ?string $scope = null;
    private ?string $ip = null;

    public function __construct(?int $appId, ?int $userId, ?int $status, ?string $scope, ?string $ip, \DateTimeImmutable $from, \DateTimeImmutable $to, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        parent::__construct($from, $to, $startIndex, $count, $search, $sortBy, $sortOrder);

        $this->appId = $appId;
        $this->userId = $userId;
        $this->status = $status;
        $this->scope = $scope;
        $this->ip = $ip;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getIp(): ?string
    {
        return $this->ip;
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

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        $arguments = parent::getConstructorArguments($request);

        $appId = self::toInt($request->get('appId'));
        $userId = self::toInt($request->get('userId'));
        $status = self::toInt($request->get('status'));
        $scope = $request->get('scope');
        $ip = $request->get('ip');

        $arguments['appId'] = $appId;
        $arguments['userId'] = $userId;
        $arguments['status'] = $status;
        $arguments['scope'] = $scope;
        $arguments['ip'] = $ip;

        return $arguments;
    }
}
