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
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class App
{
    private Table\App $appTable;
    private Table\Scope $scopeTable;
    private Table\App\Scope $appScopeTable;
    private Table\App\Token $appTokenTable;
    private string $tokenSecret;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\App $appTable, Table\Scope $scopeTable, Table\App\Scope $appScopeTable, Table\App\Token $appTokenTable, ConfigInterface $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->appTable        = $appTable;
        $this->scopeTable      = $scopeTable;
        $this->appScopeTable   = $appScopeTable;
        $this->appTokenTable   = $appTokenTable;
        $this->tokenSecret     = $config->get('fusio_project_key');
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(AppCreate $app, UserContext $context): int
    {
        // check whether app exists
        $condition  = new Condition();
        $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $app->getUserId());
        $condition->notEquals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_DELETED);
        $condition->equals(Table\Generated\AppTable::COLUMN_NAME, $app->getName());

        $existing = $this->appTable->findOneBy($condition);
        if (!empty($existing)) {
            throw new StatusCode\BadRequestException('App already exists');
        }

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

            $record = new Table\Generated\AppRow([
                Table\Generated\AppTable::COLUMN_USER_ID => $app->getUserId(),
                Table\Generated\AppTable::COLUMN_STATUS => $app->getStatus(),
                Table\Generated\AppTable::COLUMN_NAME => $app->getName(),
                Table\Generated\AppTable::COLUMN_URL => $app->getUrl(),
                Table\Generated\AppTable::COLUMN_PARAMETERS => $parameters,
                Table\Generated\AppTable::COLUMN_APP_KEY => $appKey,
                Table\Generated\AppTable::COLUMN_APP_SECRET => $appSecret,
                Table\Generated\AppTable::COLUMN_METADATA => $app->getMetadata() !== null ? json_encode($app->getMetadata()) : null,
                Table\Generated\AppTable::COLUMN_DATE => new DateTime(),
            ]);

            $this->appTable->create($record);

            $appId = $this->appTable->getLastInsertId();
            $app->setId($appId);

            $scopes = $app->getScopes();
            if ($scopes !== null) {
                $this->insertScopes($appId, $scopes);
            }

            $this->appTable->commit();
        } catch (\Throwable $e) {
            $this->appTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($app, $context));

        return $appId;
    }

    public function update(int $appId, AppUpdate $app, UserContext $context): int
    {
        $existing = $this->appTable->find($appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing->getStatus() == Table\App::STATUS_DELETED) {
            throw new StatusCode\GoneException('App was deleted');
        }

        // parse parameters
        $parameters = $app->getParameters();
        if ($parameters !== null) {
            $parameters = $this->parseParameters($parameters);
        } else {
            $parameters = $existing->getParameters();
        }

        try {
            $this->appTable->beginTransaction();

            $record = new Table\Generated\AppRow([
                Table\Generated\AppTable::COLUMN_ID => $existing->getId(),
                Table\Generated\AppTable::COLUMN_STATUS => $app->getStatus(),
                Table\Generated\AppTable::COLUMN_NAME => $app->getName(),
                Table\Generated\AppTable::COLUMN_URL => $app->getUrl(),
                Table\Generated\AppTable::COLUMN_PARAMETERS => $parameters,
                Table\Generated\AppTable::COLUMN_METADATA => $app->getMetadata() !== null ? json_encode($app->getMetadata()) : null,
            ]);

            $this->appTable->update($record);

            $scopes = $app->getScopes();
            if ($scopes !== null) {
                // delete existing scopes
                $this->appScopeTable->deleteAllFromApp($existing->getId());

                // insert scopes
                $this->insertScopes($existing->getId(), $scopes);
            }

            $this->appTable->commit();
        } catch (\Throwable $e) {
            $this->appTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($app, $existing, $context));

        return $appId;
    }

    public function delete(int $appId, UserContext $context): int
    {
        $existing = $this->appTable->find($appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing->getStatus() == Table\App::STATUS_DELETED) {
            throw new StatusCode\GoneException('App was deleted');
        }

        $record = new Table\Generated\AppRow([
            Table\Generated\AppTable::COLUMN_ID => $existing->getId(),
            Table\Generated\AppTable::COLUMN_STATUS => Table\App::STATUS_DELETED,
        ]);

        $this->appTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $appId;
    }

    protected function insertScopes(int $appId, ?array $scopes): void
    {
        if (!empty($scopes)) {
            $scopes = $this->scopeTable->getValidScopes($scopes);

            foreach ($scopes as $scope) {
                $this->appScopeTable->create(new Table\Generated\AppScopeRow([
                    Table\Generated\AppScopeTable::COLUMN_APP_ID => $appId,
                    Table\Generated\AppScopeTable::COLUMN_SCOPE_ID => $scope->getId(),
                ]));
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
