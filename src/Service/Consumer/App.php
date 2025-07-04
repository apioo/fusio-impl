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

namespace Fusio\Impl\Service\Consumer;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Consumer\AppCreate;
use Fusio\Model\Consumer\AppUpdate;
use PSX\Http\Exception as StatusCode;

/**
 * App
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

    public function __construct(Service\App $appService, Service\Config $configService, Table\App $appTable, Table\Scope $scopeTable)
    {
        $this->appService = $appService;
        $this->configService = $configService;
        $this->appTable = $appTable;
        $this->scopeTable = $scopeTable;
    }

    public function create(AppCreate $app, UserContext $context): int
    {
        $this->assertMaxAppCount($context);
        $this->assertName($app->getName());
        $this->assertUrl($app->getUrl());

        $rawScopes = $app->getScopes() ?? [];
        $rawScopes[] = 'authorization'; // automatically add the authorization scope which a user can not select

        $scopes = $this->scopeTable->getValidUserScopes($context->getTenantId(), $context->getUserId(), $rawScopes);
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

    public function update(string $appId, AppUpdate $app, UserContext $context): int
    {
        $existing = $this->appTable->findOneByIdentifier($context->getTenantId(), $appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('App does not belong to the user');
        }

        // validate data
        $this->assertName($app->getName());
        $this->assertUrl($app->getUrl());

        $scopes = $this->scopeTable->getValidUserScopes($context->getTenantId(), $context->getUserId(), $app->getScopes());
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
        }

        $backendApp = new Model\Backend\AppUpdate();
        $backendApp->setName($app->getName());
        $backendApp->setUrl($app->getUrl());
        $backendApp->setScopes($scopes);

        return $this->appService->update((string) $existing->getId(), $backendApp, $context);
    }

    public function delete(string $appId, UserContext $context): int
    {
        $existing = $this->appTable->findOneByIdentifier($context->getTenantId(), $appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('App does not belong to the user');
        }

        return $this->appService->delete((string) $existing->getId(), $context);
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

    private function assertMaxAppCount(UserContext $context): void
    {
        $count = $this->appTable->getCountForUser($context->getTenantId(), $context->getUserId());
        if ($count > $this->configService->getValue('consumer_max_apps')) {
            throw new StatusCode\BadRequestException('Maximal amount of apps reached. Please delete another app in order to create a new one');
        }
    }
}
