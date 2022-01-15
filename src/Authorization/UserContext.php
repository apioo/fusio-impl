<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Authorization;

use Fusio\Engine\ContextInterface;

/**
 * UserContext
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class UserContext
{
    private int $userId;
    private int $appId;
    private string $ip;

    public function __construct(int $userId, int $appId, string $ip)
    {
        $this->userId = $userId;
        $this->appId  = $appId;
        $this->ip     = $ip;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAppId(): int
    {
        return $this->appId;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public static function newContext(int $userId, ?int $appId = null): self
    {
        return new UserContext($userId, $appId ?? 1, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public static function newAnonymousContext(): self
    {
        return self::newContext(1, 1);
    }

    public static function newCommandContext(): self
    {
        return self::newContext(1, 1);
    }

    public static function newActionContext(ContextInterface $context): self
    {
        return self::newContext($context->getUser()->getId(), $context->getApp()->getId());
    }
}
