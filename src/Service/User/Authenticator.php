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

namespace Fusio\Impl\Service\User;

use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\User\FailedAuthenticationEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\UserActivate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Authenticator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Authenticator
{
    private Table\User $userTable;
    private Table\User\Scope $userScopeTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\User $userTable, Table\User\Scope $userScopeTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->userTable = $userTable;
        $this->userScopeTable = $userScopeTable;
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

        $condition = Condition::withAnd();
        $condition->equals($column, $username);
        $condition->equals(Table\Generated\UserTable::COLUMN_STATUS, Table\User::STATUS_ACTIVE);

        $user = $this->userTable->findOneBy($condition);
        if (empty($user)) {
            return null;
        }

        // we can authenticate only local users
        if ($user->getProvider() != ProviderInterface::PROVIDER_SYSTEM) {
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
