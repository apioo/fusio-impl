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

namespace Fusio\Impl\Backend\Filter\Plan\Usage;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use PSX\Sql\Condition;

/**
 * UsageQueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UsageQueryFilter extends DateQueryFilter
{
    private ?int $operationId = null;
    private ?int $userId = null;
    private ?int $appId = null;

    public function __construct(?int $operationId, ?int $userId, ?int $appId, \DateTimeImmutable $from, \DateTimeImmutable $to, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        parent::__construct($from, $to, $startIndex, $count, $search, $sortBy, $sortOrder);

        $this->operationId = $operationId;
        $this->userId = $userId;
        $this->appId = $appId;
    }

    public function getOperationId(): ?int
    {
        return $this->operationId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function getCondition(array $columnMapping, ?string $alias = null): Condition
    {
        $condition = parent::getCondition($columnMapping, $alias);
        $alias = $this->getAlias($alias);

        if (!empty($this->operationId)) {
            $condition->equals($alias . 'route_id', $this->operationId);
        }

        if (!empty($this->userId)) {
            $condition->equals($alias . 'user_id', $this->userId);
        }

        if (!empty($this->appId)) {
            $condition->equals($alias . 'app_id', $this->appId);
        }

        return $condition;
    }

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        $arguments = parent::getConstructorArguments($request);

        $operationId = self::toInt($request->get('operationId'));
        $userId = self::toInt($request->get('userId'));
        $appId = self::toInt($request->get('appId'));

        $arguments['operationId'] = $operationId;
        $arguments['userId'] = $userId;
        $arguments['appId'] = $appId;

        return $arguments;
    }
}
