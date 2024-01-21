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

namespace Fusio\Impl\Service\Consumer;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Consumer\AppCreate;
use Fusio\Model\Consumer\AppUpdate;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Developer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class App
{
    private Service\App $appService;
    private Service\Config $configService;
    private Table\App $appTable;
    private Table\Scope $scopeTable;
    private Table\User\Scope $userScopeTable;

    public function __construct(Service\App $appService, Service\Config $configService, Table\App $appTable, Table\Scope $scopeTable, Table\User\Scope $userScopeTable)
    {
        $this->appService     = $appService;
        $this->configService  = $configService;
        $this->appTable       = $appTable;
        $this->scopeTable     = $scopeTable;
        $this->userScopeTable = $userScopeTable;
    }

    public function create(AppCreate $app, UserContext $context): int
    {
        $this->assertName($app->getName());
        $this->assertUrl($app->getUrl());
        $this->assertMaxAppCount($context->getUserId());

        $rawScopes = $app->getScopes() ?? [];
        $rawScopes[] = 'authorization'; // automatically add the authorization scope which a user can not select

        $scopes = $this->getValidUserScopes($context->getUserId(), $rawScopes, $context->getTenantId());
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
        }

        $appApproval = $this->configService->getValue('app_approval');

        $backendApp = new Model\Backend\AppCreate();
        $backendApp->setUserId($context->getUserId());
        $backendApp->setStatus($appApproval === false ? Table\App::STATUS_ACTIVE : Table\App::STATUS_PENDING);
        $backendApp->setName($app->getName());
        $backendApp->setUrl($app->getUrl());
        $backendApp->setScopes($scopes);

        return $this->appService->create($backendApp, $context);
    }

    public function update(int $appId, AppUpdate $app, UserContext $context): int
    {
        $existing = $this->appTable->find($appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('App does not belong to the user');
        }

        // validate data
        $this->assertName($app->getName());
        $this->assertUrl($app->getUrl());

        $scopes = $this->getValidUserScopes($context->getUserId(), $app->getScopes(), $context->getTenantId());
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
        }

        $backendApp = new Model\Backend\AppUpdate();
        $backendApp->setName($app->getName());
        $backendApp->setUrl($app->getUrl());
        $backendApp->setScopes($scopes);

        return $this->appService->update((string) $appId, $backendApp, $context);
    }

    public function delete(int $appId, UserContext $context): int
    {
        $userId = $context->getUserId();
        $app    = $this->appTable->find($appId);

        if (empty($app)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($app->getUserId() != $userId) {
            throw new StatusCode\BadRequestException('App does not belong to the user');
        }

        return $this->appService->delete((string) $appId, $context);
    }

    protected function getValidUserScopes(int $userId, ?array $scopes, ?string $tenantId = null): array
    {
        if (empty($scopes)) {
            return [];
        }

        $userScopes = $this->userScopeTable->getAvailableScopes($userId);
        $scopes     = $this->scopeTable->getValidScopes($scopes, $tenantId);

        // check that the user can assign only the scopes which are also
        // assigned to the user account
        $scopes = array_filter($scopes, function (Table\Generated\ScopeRow $scope) use ($userScopes) {
            foreach ($userScopes as $userScope) {
                if ($userScope['id'] == $scope->getId()) {
                    return true;
                }
            }
            return false;
        });

        return array_map(function (Table\Generated\ScopeRow $scope) {
            return $scope->getName();
        }, $scopes);
    }

    private function assertName(?string $name): void
    {
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Invalid name');
        }
    }

    private function assertUrl(?string $url): void
    {
        if (!empty($url)) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new StatusCode\BadRequestException('Invalid url format');
            }
        }
    }

    private function assertMaxAppCount(int $userId): void
    {
        $appCount = $this->configService->getValue('app_consumer');

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $userId);
        $condition->in(Table\Generated\AppTable::COLUMN_STATUS, [Table\App::STATUS_ACTIVE, Table\App::STATUS_PENDING, Table\App::STATUS_DEACTIVATED]);

        if ($this->appTable->getCount($condition) > $appCount) {
            throw new StatusCode\BadRequestException('Maximal amount of apps reached. Please delete another app in order to register a new one');
        }
    }
}
