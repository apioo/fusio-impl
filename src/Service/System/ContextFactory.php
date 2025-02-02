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

namespace Fusio\Impl\Service\System;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use PSX\Http\Exception\InternalServerErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

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
    private Table\App $appTable;
    private Table\Role $roleTable;
    private FrameworkConfig $frameworkConfig;

    public function __construct(Table\Category $categoryTable, Table\User $userTable, Table\App $appTable, Table\Role $roleTable, FrameworkConfig $frameworkConfig)
    {
        $this->categoryTable = $categoryTable;
        $this->userTable = $userTable;
        $this->appTable = $appTable;
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

    public function newCommandContext(InputInterface $input): UserContext
    {
        $tenantId = $this->frameworkConfig->getTenantId();
        if (empty($tenantId)) {
            // in case we are on the root tenant we have the option to select a tenant otherwise we use the configured tenant
            $tenantId = $input->getOption('tenant');
        }

        $category = $input->getOption('category');
        if (empty($category)) {
            $categoryId = $this->categoryTable->getCategoryIdByType($tenantId, Table\Category::TYPE_SYSTEM);
        } elseif (is_numeric($category)) {
            $categoryId = $this->categoryTable->findOneByTenantAndId($tenantId, (int) $category)?->getId() ?? throw new \RuntimeException('Provided category does not exist');
        } else {
            $categoryId = $this->categoryTable->findOneByTenantAndName($tenantId, $category)?->getId() ?? throw new \RuntimeException('Provided category does not exist');
        }

        $user = $input->getOption('user');
        if (empty($user)) {
            $userId = $this->userTable->findOneByTenantAndName($tenantId, 'Administrator')?->getId() ?? throw new \RuntimeException('Default Administrator user is missing');
        } elseif (is_numeric($user)) {
            $userId = $this->userTable->findOneByTenantAndId($tenantId, (int) $user)?->getId() ?? throw new \RuntimeException('Provided user does not exist');
        } else {
            $userId = $this->userTable->findOneByTenantAndName($tenantId, $user)?->getId() ?? throw new \RuntimeException('Provided user does not exist');
        }

        $app = $input->getOption('app');
        if (empty($app)) {
            $appId = null;
        } elseif (is_numeric($user)) {
            $appId = $this->appTable->findOneByTenantAndId($tenantId, (int) $app)?->getId() ?? throw new \RuntimeException('Provided app does not exist');
        } else {
            $appId = $this->appTable->findOneByTenantAndName($tenantId, $app)?->getId() ?? throw new \RuntimeException('Provided app does not exist');
        }

        return UserContext::newContext($categoryId, $userId, $appId, $tenantId);
    }

    public function addContextOptions(Command $command): void
    {
        $command->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Optional the tenant for this context');
        $command->addOption('category', null, InputOption::VALUE_REQUIRED, 'Optional the category id or name for this context');
        $command->addOption('user', null, InputOption::VALUE_REQUIRED, 'Optional the user id or name for this context');
        $command->addOption('app', null, InputOption::VALUE_REQUIRED, 'Optional the app id or name for this context');
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
        $appId = null;
        if (!$context->getApp()->isAnonymous()) {
            $appId = $context->getApp()->getId();
        }

        return UserContext::newContext($context->getUser()->getCategoryId(), $context->getUser()->getId(), $appId, $context->getTenantId());
    }
}
