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

namespace Fusio\Impl\Backend\Filter\Plan\Usage;

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
    protected ?int $userId = null;
    protected ?int $appId = null;

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to, ?int $routeId = null, ?int $userId = null, ?int $appId = null)
    {
        parent::__construct($from, $to);

        $this->routeId = $routeId;
        $this->userId = $userId;
        $this->appId = $appId;
    }

    public function getRouteId(): ?int
    {
        return $this->routeId;
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

    protected function getDateColumn(): string
    {
        return 'insert_date';
    }

    public static function create(RequestInterface $request): self
    {
        [$from, $to] = self::getFromAndTo($request);

        $routeId = self::toInt($request->get('routeId'));
        $userId  = self::toInt($request->get('userId'));
        $appId   = self::toInt($request->get('appId'));

        return new self($from, $to, $routeId, $userId, $appId);
    }
}
