<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Framework\Loader;

use Fusio\Engine\Model\AppInterface;
use Fusio\Engine\Model\TokenInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Impl\Table\Generated\OperationRow;
use PSX\Framework\Loader\Context as FrameworkContext;

/**
 * Context
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Context extends FrameworkContext
{
    private ?string $tenantId = null;
    private ?int $categoryId = null;
    private ?AppInterface $app = null;
    private ?UserInterface $user = null;
    private ?TokenInterface $token = null;
    private ?int $logId = null;
    private ?OperationRow $operation = null;
    private bool $cli = false;

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getCategoryId(): int
    {
        if ($this->categoryId === null) {
            throw new ContextPropertyNotSetException('categoryId');
        }

        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getApp(): AppInterface
    {
        if ($this->app === null) {
            throw new ContextPropertyNotSetException('app');
        }

        return $this->app;
    }

    public function getAppId(): ?int
    {
        if ($this->app === null) {
            return null;
        }

        if ($this->app->isAnonymous()) {
            return null;
        }

        return $this->app->getId();
    }

    public function setApp(AppInterface $app): void
    {
        $this->app = $app;
    }

    public function getUser(): UserInterface
    {
        if ($this->user === null) {
            throw new ContextPropertyNotSetException('user');
        }

        return $this->user;
    }

    public function getUserId(): ?int
    {
        if ($this->user === null) {
            return null;
        }

        if ($this->user->isAnonymous()) {
            return null;
        }

        return $this->user->getId();
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getToken(): TokenInterface
    {
        if ($this->token === null) {
            throw new ContextPropertyNotSetException('token');
        }

        return $this->token;
    }

    public function setToken(TokenInterface $token): void
    {
        $this->token = $token;
    }

    public function getLogId(): int
    {
        if ($this->logId === null) {
            throw new ContextPropertyNotSetException('logId');
        }

        return $this->logId;
    }

    public function setLogId(int $logId): void
    {
        $this->logId = $logId;
    }

    public function getOperation(): OperationRow
    {
        if ($this->operation === null) {
            throw new ContextPropertyNotSetException('operation');
        }

        return $this->operation;
    }

    public function setOperation(OperationRow $operation): void
    {
        $this->operation = $operation;
    }

    public function isCli(): bool
    {
        return $this->cli;
    }

    public function setCli(bool $cli): void
    {
        $this->cli = $cli;
    }
}
