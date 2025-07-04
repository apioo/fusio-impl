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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * Authenticator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Authenticator
{
    public function __construct(
        private Table\User $userTable,
        private Table\User\Scope $userScopeTable,
        private Service\System\FrameworkConfig $frameworkConfig,
    ) {
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

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
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
        }

        return null;
    }

    public function getValidScopes(?string $tenantId, int $userId, array $scopes): array
    {
        return Table\Scope::getNames($this->userScopeTable->getValidScopes($tenantId, $userId, $scopes));
    }

    public function getAvailableScopes(?string $tenantId, int $userId): array
    {
        return Table\Scope::getNames($this->userScopeTable->getAvailableScopes($tenantId, $userId));
    }
}
