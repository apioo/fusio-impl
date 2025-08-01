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

namespace Fusio\Impl\Authorization;

/**
 * UserContext
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserContext
{
    private int $categoryId;
    private int $userId;
    private ?int $appId;
    private string $ip;
    private ?string $tenantId;

    public function __construct(int $categoryId, int $userId, ?int $appId, string $ip, ?string $tenantId = null)
    {
        $this->categoryId = $categoryId;
        $this->userId = $userId;
        $this->appId = $appId;
        $this->ip = $ip;
        $this->tenantId = $tenantId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public static function newContext(int $categoryId, int $userId, ?int $appId = null, ?string $tenantId = null): self
    {
        return new UserContext(
            $categoryId,
            $userId,
            $appId,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $tenantId
        );
    }
}
