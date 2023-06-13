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

namespace Fusio\Impl\Authorization;

use Fusio\Engine\ContextInterface;

/**
 * UserContext
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
