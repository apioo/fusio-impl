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

namespace Fusio\Impl\Service\User;

use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\User\FailedAuthenticationEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\UserActivate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Authenticator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Authenticator
{
    private Table\User $userTable;
    private Table\User\Scope $userScopeTable;
    private ConfigInterface $config;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\User $userTable, Table\User\Scope $userScopeTable, ConfigInterface $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->userTable = $userTable;
        $this->userScopeTable = $userScopeTable;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Authenticates a user based on the username and password. Returns the user id if the authentication was successful
     * else null
     */
    public function authenticate(string $username, string $password): ?int
    {
        if (empty($password)) {
            return null;
        }

        // allow login either through username or email
        if (preg_match('/^[a-zA-Z0-9\-\_\.]{3,32}$/', $username)) {
            $column = Table\Generated\UserTable::COLUMN_NAME;
        } else {
            $column = Table\Generated\UserTable::COLUMN_EMAIL;
        }

        $tenantId = $this->config->get('fusio_tenant_id');

        $condition = Condition::withAnd();
        if (!empty($tenantId)) {
            $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $tenantId);
        }
        $condition->equals($column, $username);
        $condition->equals(Table\Generated\UserTable::COLUMN_STATUS, Table\User::STATUS_ACTIVE);

        $user = $this->userTable->findOneBy($condition);
        if (empty($user)) {
            return null;
        }

        // we can authenticate only local users
        $identityId = $user->getIdentityId();
        if (!empty($identityId)) {
            return null;
        }

        // check password
        $databasePassword = $user->getPassword();
        if (empty($databasePassword)) {
            return null;
        }

        if (password_verify($password, $databasePassword)) {
            return $user->getId();
        } else {
            $this->eventDispatcher->dispatch(new FailedAuthenticationEvent(UserContext::newContext($user->getId())));
        }

        return null;
    }

    public function getValidScopes(int $userId, array $scopes): array
    {
        return Table\Scope::getNames($this->userScopeTable->getValidScopes($userId, $scopes));
    }

    public function getAvailableScopes(int $userId): array
    {
        return Table\Scope::getNames($this->userScopeTable->getAvailableScopes($userId));
    }
}
