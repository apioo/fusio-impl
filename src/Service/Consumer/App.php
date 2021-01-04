<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Model\Consumer\App_Create;
use Fusio\Model\Consumer\App_Update;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Developer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class App
{
    /**
     * @var \Fusio\Impl\Service\App
     */
    private $appService;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    private $configService;

    /**
     * @var \Fusio\Impl\Table\App
     */
    private $appTable;

    /**
     * @var \Fusio\Impl\Table\Scope
     */
    private $scopeTable;

    /**
     * @var \Fusio\Impl\Table\User\Scope
     */
    private $userScopeTable;

    /**
     * @param \Fusio\Impl\Service\App $appService
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Fusio\Impl\Table\App $appTable
     * @param \Fusio\Impl\Table\Scope $scopeTable
     * @param \Fusio\Impl\Table\User\Scope $userScopeTable
     */
    public function __construct(Service\App $appService, Service\Config $configService, Table\App $appTable, Table\Scope $scopeTable, Table\User\Scope $userScopeTable)
    {
        $this->appService     = $appService;
        $this->configService  = $configService;
        $this->appTable       = $appTable;
        $this->scopeTable     = $scopeTable;
        $this->userScopeTable = $userScopeTable;
    }

    public function create(App_Create $app, UserContext $context)
    {
        $this->assertName($app->getName());
        $this->assertUrl($app->getUrl());
        $this->assertMaxAppCount($context->getUserId());

        $scopes = $this->getValidUserScopes($context->getUserId(), $app->getScopes());
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
        }

        $appApproval = $this->configService->getValue('app_approval');

        $backendApp = new \Fusio\Model\Backend\App_Create();
        $backendApp->setUserId($context->getUserId());
        $backendApp->setStatus($appApproval === false ? Table\App::STATUS_ACTIVE : Table\App::STATUS_PENDING);
        $backendApp->setName($app->getName());
        $backendApp->setUrl($app->getUrl());
        $backendApp->setScopes($scopes);

        $this->appService->create($backendApp, $context);
    }

    public function update(int $appId, App_Update $app, UserContext $context)
    {
        $existing = $this->appTable->get($appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing['user_id'] != $context->getUserId()) {
            throw new StatusCode\BadRequestException('App does not belong to the user');
        }

        // validate data
        $this->assertName($app->getName());
        $this->assertUrl($app->getUrl());

        $scopes = $this->getValidUserScopes($context->getUserId(), $app->getScopes());
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
        }

        $backendApp = new \Fusio\Model\Backend\App_Update();
        $backendApp->setName($app->getName());
        $backendApp->setUrl($app->getUrl());
        $backendApp->setScopes($scopes);

        $this->appService->update($appId, $backendApp, $context);
    }

    public function delete($appId, UserContext $context)
    {
        $userId = $context->getUserId();
        $app    = $this->appTable->get($appId);

        if (empty($app)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($app['user_id'] != $userId) {
            throw new StatusCode\BadRequestException('App does not belong to the user');
        }

        $this->appService->delete($appId, $context);
    }

    protected function getValidUserScopes($userId, $scopes)
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

    private function assertName($name)
    {
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Invalid name');
        }
    }

    private function assertUrl($url)
    {
        if (!empty($url)) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new StatusCode\BadRequestException('Invalid url format');
            }
        }
    }

    private function assertMaxAppCount($userId)
    {
        $appCount = $this->configService->getValue('app_consumer');

        $condition = new Condition();
        $condition->equals('user_id', $userId);
        $condition->in('status', [Table\App::STATUS_ACTIVE, Table\App::STATUS_PENDING, Table\App::STATUS_DEACTIVATED]);

        if ($this->appTable->getCount($condition) > $appCount) {
            throw new StatusCode\BadRequestException('Maximal amount of apps reached. Please delete another app in order to register a new one');
        }
    }
}
