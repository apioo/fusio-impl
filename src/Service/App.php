<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Model\Backend\App_Create;
use Fusio\Model\Backend\App_Update;
use PSX\DateTime\DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function __construct(Table\App $appTable, Table\Scope $scopeTable, Table\App\Scope $appScopeTable, Table\App\Token $appTokenTable, string $tokenSecret, EventDispatcherInterface $eventDispatcher)
    {
        $this->appTable        = $appTable;
        $this->scopeTable      = $scopeTable;
        $this->appScopeTable   = $appScopeTable;
        $this->appTokenTable   = $appTokenTable;
        $this->tokenSecret     = $tokenSecret;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(App_Create $app, UserContext $context): int
    {
        // check whether app exists
        $condition  = new Condition();
        $condition->equals('user_id', $app->getUserId());
        $condition->notEquals('status', Table\App::STATUS_DELETED);
        $condition->equals('name', $app->getName());

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
                'user_id'    => $app->getUserId(),
                'status'     => $app->getStatus(),
                'name'       => $app->getName(),
                'url'        => $app->getUrl(),
                'parameters' => $parameters,
                'app_key'    => $appKey,
                'app_secret' => $appSecret,
                'date'       => new DateTime(),
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

    public function update(int $appId, App_Update $app, UserContext $context): int
    {
        $existing = $this->appTable->find($appId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find app');
        }

        if ($existing['status'] == Table\App::STATUS_DELETED) {
            throw new StatusCode\GoneException('App was deleted');
        }

        // parse parameters
        $parameters = $app->getParameters();
        if ($parameters !== null) {
            $parameters = $this->parseParameters($parameters);
        } else {
            $parameters = $existing['parameters'];
        }

        try {
            $this->appTable->beginTransaction();

            $record = new Table\Generated\AppRow([
                'id'         => $existing['id'],
                'status'     => $app->getStatus(),
                'name'       => $app->getName(),
                'url'        => $app->getUrl(),
                'parameters' => $parameters,
            ]);

            $this->appTable->update($record);

            $scopes = $app->getScopes();
            if ($scopes !== null) {
                // delete existing scopes
                $this->appScopeTable->deleteAllFromApp($existing['id']);

                // insert scopes
                $this->insertScopes($existing['id'], $scopes);
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

        if ($existing['status'] == Table\App::STATUS_DELETED) {
            throw new StatusCode\GoneException('App was deleted');
        }

        $record = new Table\Generated\AppRow([
            'id'     => $existing['id'],
            'status' => Table\App::STATUS_DELETED,
        ]);

        $this->appTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $appId;
    }

    protected function insertScopes(int $appId, ?array $scopes): void
    {
        if (!empty($scopes) && is_array($scopes)) {
            $scopes = $this->scopeTable->getValidScopes($scopes);

            foreach ($scopes as $scope) {
                $this->appScopeTable->create(new Table\Generated\AppScopeRow([
                    'app_id'   => $appId,
                    'scope_id' => $scope['id'],
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
