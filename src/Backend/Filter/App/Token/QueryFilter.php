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

namespace Fusio\Impl\Backend\Filter\App\Token;

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
    protected ?int $appId = null;
    protected ?int $userId = null;
    protected ?int $status = null;
    protected ?string $scope = null;
    protected ?string $ip = null;

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to, ?int $appId = null, ?int $userId = null, ?int $status = null, ?string $scope = null, ?string $ip = null)
    {
        parent::__construct($from, $to);

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

    public function getCondition(?string $alias = null): Condition
    {
        $condition = parent::getCondition($alias);
        $alias     = $alias !== null ? $alias . '.' : '';

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

    public static function create(RequestInterface $request): self
    {
        [$from, $to] = self::getFromAndTo($request);

        $appId  = self::toInt($request->get('appId'));
        $userId = self::toInt($request->get('userId'));
        $status = self::toInt($request->get('status'));
        $scope  = $request->get('scope');
        $ip     = $request->get('ip');
        $search = $request->get('search');

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

        return new self($from, $to, $appId, $userId, $status, $scope, $ip);
    }
}
