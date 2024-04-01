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

namespace Fusio\Impl\Service\System;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use PSX\Http\Exception\BadRequestException;
use PSX\Http\Exception\InternalServerErrorException;

/**
 * ContextFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ContextFactory
{
    private Table\Category $categoryTable;
    private Table\User $userTable;
    private Table\Role $roleTable;
    private FrameworkConfig $frameworkConfig;

    public function __construct(Table\Category $categoryTable, Table\User $userTable, Table\Role $roleTable, FrameworkConfig $frameworkConfig)
    {
        $this->categoryTable = $categoryTable;
        $this->userTable = $userTable;
        $this->roleTable = $roleTable;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function newAnonymousContext(): UserContext
    {
        $tenantId = $this->frameworkConfig->getTenantId();
        $categoryId = $this->categoryTable->getCategoryIdByType($tenantId, Table\Category::TYPE_CONSUMER);
        $userId = $this->userTable->findOneByTenantAndName($tenantId, 'Administrator')?->getId();
        if ($userId === null) {
            throw new InternalServerErrorException('Default Administrator user is missing');
        }

        return UserContext::newContext($categoryId, $userId, null, $tenantId);
    }

    public function newCommandContext(): UserContext
    {
        $tenantId = $this->frameworkConfig->getTenantId();
        $categoryId = $this->categoryTable->getCategoryIdByType($tenantId, Table\Category::TYPE_SYSTEM);
        $userId = $this->userTable->findOneByTenantAndName($tenantId, 'Administrator')?->getId();
        if ($userId === null) {
            throw new InternalServerErrorException('Default Administrator user is missing');
        }

        return UserContext::newContext($categoryId, $userId, null, $this->frameworkConfig->getTenantId());
    }

    public function newUserContext(Table\Generated\UserRow $user): UserContext
    {
        $tenantId = $this->frameworkConfig->getTenantId();
        $role = $this->roleTable->findOneByTenantAndId($tenantId, $user->getRoleId());
        if ($role === null) {
            throw new InternalServerErrorException('User has no assigned role');
        }

        return UserContext::newContext($role->getCategoryId(), $user->getId(), null, $tenantId);
    }

    public function newActionContext(ContextInterface $context): UserContext
    {
        if ($context->getUser()->isAnonymous()) {
            throw new BadRequestException('This action can only be invoked by authenticated users');
        }

        $appId = null;
        if (!$context->getApp()->isAnonymous()) {
            $appId = $context->getApp()->getId();
        }

        return UserContext::newContext($context->getUser()->getCategoryId(), $context->getUser()->getId(), $appId, $context->getTenantId());
    }
}
