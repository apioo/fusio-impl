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

namespace Fusio\Impl\Backend\Filter\Plan\Usage;

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
    protected ?int $operationId = null;
    protected ?int $userId = null;
    protected ?int $appId = null;

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to, ?int $operationId = null, ?int $userId = null, ?int $appId = null)
    {
        parent::__construct($from, $to);

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

    public function getCondition(?string $alias = null): Condition
    {
        $condition = parent::getCondition($alias);
        $alias     = $alias !== null ? $alias . '.' : '';

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

    protected function getDateColumn(): string
    {
        return 'insert_date';
    }

    public static function create(RequestInterface $request): self
    {
        [$from, $to] = self::getFromAndTo($request);

        $operationId = self::toInt($request->get('operationId'));
        $userId = self::toInt($request->get('userId'));
        $appId = self::toInt($request->get('appId'));

        return new self($from, $to, $operationId, $userId, $appId);
    }
}
