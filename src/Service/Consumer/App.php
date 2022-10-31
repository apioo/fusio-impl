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
 * @license http://www.gnu.org/licenses/agpl-3.0
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

        $scopes = $this->getValidUserScopes($context->getUserId(), $app->getScopes());
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

        $scopes = $this->getValidUserScopes($context->getUserId(), $app->getScopes());
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
        }

        $backendApp = new Model\Backend\AppUpdate();
        $backendApp->setName($app->getName());
        $backendApp->setUrl($app->getUrl());
        $backendApp->setScopes($scopes);

        return $this->appService->update($appId, $backendApp, $context);
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

        return $this->appService->delete($appId, $context);
    }

    protected function getValidUserScopes(int $userId, ?array $scopes): array
    {
        if (empty($scopes)) {
            return [];
        }

        $userScopes = $this->userScopeTable->getAvailableScopes($userId);
        $scopes     = $this->scopeTable->getValidScopes($scopes);

        // check that the user can assign only the scopes which are also
        // assigned to the user account
        $scopes = array_filter($scopes, function ($scope) use ($userScopes) {
            foreach ($userScopes as $userScope) {
                if ($userScope['id'] == $scope['id']) {
                    return true;
                }
            }
            return false;
        });

        return array_map(function ($scope) {
            return $scope['name'];
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

        $condition = new Condition();
        $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $userId);
        $condition->in(Table\Generated\AppTable::COLUMN_STATUS, [Table\App::STATUS_ACTIVE, Table\App::STATUS_PENDING, Table\App::STATUS_DEACTIVATED]);

        if ($this->appTable->getCount($condition) > $appCount) {
            throw new StatusCode\BadRequestException('Maximal amount of apps reached. Please delete another app in order to register a new one');
        }
    }
}
