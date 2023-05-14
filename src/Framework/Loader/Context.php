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

namespace Fusio\Impl\Framework\Loader;

use Fusio\Engine\Model\AppInterface;
use Fusio\Engine\Model\TokenInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table\Generated\OperationRow;
use PSX\Framework\Loader\Context as FrameworkContext;

/**
 * Context
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Context extends FrameworkContext
{
    private ?int $operationId = null;
    private ?int $categoryId = null;
    private ?AppInterface $app = null;
    private ?UserInterface $user = null;
    private ?TokenInterface $token = null;
    private ?int $logId = null;
    private ?OperationRow $operation = null;
    private ?UserContext $userContext = null;

    public function getOperationId(): int
    {
        if ($this->operationId === null) {
            throw new \RuntimeException('Context route id not available');
        }

        return $this->operationId;
    }

    public function setOperationId(int $operationId): void
    {
        $this->operationId = $operationId;
    }

    public function getCategoryId(): int
    {
        if ($this->categoryId === null) {
            throw new \RuntimeException('Context category id not available');
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
            throw new \RuntimeException('Context app not available');
        }

        return $this->app;
    }

    public function getAppId(): ?int
    {
        return $this->app?->getId();
    }

    public function setApp(AppInterface $app): void
    {
        $this->app = $app;
    }

    public function getUser(): UserInterface
    {
        if ($this->user === null) {
            throw new \RuntimeException('Context user not available');
        }

        return $this->user;
    }

    public function getUserId(): ?int
    {
        return $this->user?->getId();
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getToken(): TokenInterface
    {
        if ($this->token === null) {
            throw new \RuntimeException('Context token not available');
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
            throw new \RuntimeException('Context log id not available');
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
            throw new \RuntimeException('Context operation not available');
        }

        return $this->operation;
    }

    public function setOperation(OperationRow $operation): void
    {
        $this->operation = $operation;
    }

    public function getUserContext(): UserContext
    {
        if ($this->userContext) {
            return $this->userContext;
        }

        if ($this->user && $this->app) {
            return $this->userContext = UserContext::newContext($this->user->getId(), $this->app->getId());
        } elseif ($this->user) {
            return $this->userContext = UserContext::newContext($this->user->getId());
        } else {
            return $this->userContext = UserContext::newAnonymousContext();
        }
    }
}
