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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\App\CreatedEvent;
use Fusio\Impl\Event\App\DeletedEvent;
use Fusio\Impl\Event\App\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\AppCreate;
use Fusio\Model\Backend\AppUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
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
    private Table\App $appTable;
    private Table\Scope $scopeTable;
    private Table\App\Scope $appScopeTable;
    private App\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\App $appTable, Table\Scope $scopeTable, Table\App\Scope $appScopeTable, App\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->appTable = $appTable;
        $this->scopeTable = $scopeTable;
        $this->appScopeTable = $appScopeTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(AppCreate $app, UserContext $context): int
    {
        $this->validator->assert($app, $context->getTenantId());

        // parse parameters
        $parameters = $app->getParameters();
        if ($parameters !== null) {
            $parameters = $this->parseParameters($parameters);
        }

        // create app
        try {
            $this->appTable->beginTransaction();

            $appKey    = TokenGenerator::generateAppKey();
            $appSecret = TokenGenerator::generateAppSecret();

            $row = new Table\Generated\AppRow();
            $row->setTenantId($context->getTenantId());
            $row->setUserId($app->getUserId());
            $row->setStatus($app->getStatus());
            $row->setName($app->getName());
            $row->setUrl($app->getUrl());
            $row->setParameters($parameters);
            $row->setAppKey($appKey);
            $row->setAppSecret($appSecret);
            $row->setMetadata($app->getMetadata() !== null ? json_encode($app->getMetadata()) : null);
            $row->setDate(LocalDateTime::now());
            $this->appTable->create($row);

            $appId = $this->appTable->getLastInsertId();
            $app->setId($appId);

            $scopes = $app->getScopes();
            if ($scopes !== null) {
                $this->insertScopes($context->getTenantId(), $appId, $scopes);
            }

            $this->appTable->commit();
        } catch (\Throwable $e) {
            $this->appTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($app, $context));

        return $appId;
    }

    public function update(string $appId, AppUpdate $app, UserContext $context): int
    {
        $existing = $this->appTable->findOneByIdentifier($context->getTenantId(), $appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing->getStatus() == Table\App::STATUS_DELETED) {
            throw new StatusCode\GoneException('App was deleted');
        }

        $this->validator->assert($app, $context->getTenantId(), $existing);

        // parse parameters
        $parameters = $app->getParameters();
        if ($parameters !== null) {
            $parameters = $this->parseParameters($parameters);
        } else {
            $parameters = $existing->getParameters();
        }

        try {
            $this->appTable->beginTransaction();

            $existing->setStatus($app->getStatus() ?? Table\App::STATUS_ACTIVE);
            $existing->setName($app->getName() ?? $existing->getName());
            $existing->setUrl($app->getUrl() ?? $existing->getUrl());
            $existing->setParameters($parameters);
            $existing->setMetadata($app->getMetadata() !== null ? json_encode($app->getMetadata()) : $existing->getParameters());
            $this->appTable->update($existing);

            $scopes = $app->getScopes();
            if ($scopes !== null) {
                // delete existing scopes
                $this->appScopeTable->deleteAllFromApp($existing->getId());

                // insert scopes
                $this->insertScopes($context->getTenantId(), $existing->getId(), $scopes);
            }

            $this->appTable->commit();
        } catch (\Throwable $e) {
            $this->appTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($app, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $appId, UserContext $context): int
    {
        $existing = $this->appTable->findOneByIdentifier($context->getTenantId(), $appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing->getStatus() == Table\App::STATUS_DELETED) {
            throw new StatusCode\GoneException('App was deleted');
        }

        $existing->setStatus(Table\App::STATUS_DELETED);
        $this->appTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    protected function insertScopes(?string $tenantId, int $appId, ?array $scopes): void
    {
        if (!empty($scopes)) {
            $scopes = $this->scopeTable->getValidScopes($tenantId, $scopes);

            foreach ($scopes as $scope) {
                $row = new Table\Generated\AppScopeRow();
                $row->setAppId($appId);
                $row->setScopeId($scope->getId());
                $this->appScopeTable->create($row);
            }
        }
    }

    protected function parseParameters(string $parameters): string
    {
        parse_str($parameters, $data);

        $params = [];
        foreach ($data as $key => $value) {
            if (!ctype_alnum($key)) {
                throw new StatusCode\BadRequestException('Invalid parameter key only alnum characters are allowed');
            }
            if (!preg_match('/^[\x21-\x7E]*$/', $value)) {
                throw new StatusCode\BadRequestException('Invalid parameter value only printable ascii characters are allowed');
            }
            $params[$key] = $value;
        }

        return http_build_query($params, '', '&');
    }
}
