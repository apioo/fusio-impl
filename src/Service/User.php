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

namespace Fusio\Impl\Service;

use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Account_ChangePassword;
use Fusio\Impl\Backend\Model\User_Create;
use Fusio\Impl\Backend\Model\User_Remote;
use Fusio\Impl\Backend\Model\User_Update;
use Fusio\Impl\Event\User\ChangedPasswordEvent;
use Fusio\Impl\Event\User\ChangedStatusEvent;
use Fusio\Impl\Event\User\CreatedEvent;
use Fusio\Impl\Event\User\DeletedEvent;
use Fusio\Impl\Event\User\FailedAuthenticationEvent;
use Fusio\Impl\Event\User\UpdatedEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\DateTime\DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class User
{
    /**
     * @var \Fusio\Impl\Table\User
     */
    private $userTable;

    /**
     * @var \Fusio\Impl\Table\Scope
     */
    private $scopeTable;

    /**
     * @var \Fusio\Impl\Table\User\Scope
     */
    private $userScopeTable;

    /**
     * @var \Fusio\Impl\Table\Role\Scope
     */
    private $roleScopeTable;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    private $configService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array|null
     */
    private $userAttributes;

    /**
     * @param \Fusio\Impl\Table\User $userTable
     * @param \Fusio\Impl\Table\Scope $scopeTable
     * @param \Fusio\Impl\Table\User\Scope $userScopeTable
     * @param \Fusio\Impl\Table\Role\Scope $roleScopeTable
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param array|null $userAttributes
     */
    public function __construct(Table\User $userTable, Table\Scope $scopeTable, Table\User\Scope $userScopeTable, Table\Role\Scope $roleScopeTable, Service\Config $configService, EventDispatcherInterface $eventDispatcher, array $userAttributes = null)
    {
        $this->userTable       = $userTable;
        $this->scopeTable      = $scopeTable;
        $this->userScopeTable  = $userScopeTable;
        $this->roleScopeTable  = $roleScopeTable;
        $this->configService   = $configService;
        $this->eventDispatcher = $eventDispatcher;
        $this->userAttributes  = $userAttributes;
    }

    /**
     * Authenticates a user based on the username and password. Returns the user
     * id if the authentication was successful else null
     *
     * @param string $username
     * @param string $password
     * @return integer|null
     */
    public function authenticateUser($username, $password)
    {
        if (empty($password)) {
            return null;
        }

        // allow login either through username or email
        if (preg_match('/^[a-zA-Z0-9\-\_\.]{3,32}$/', $username)) {
            $column = 'name';
        } else {
            $column = 'email';
        }

        $condition = new Condition();
        $condition->equals($column, $username);
        $condition->equals('status', Table\User::STATUS_ACTIVE);

        $user = $this->userTable->getOneBy($condition);

        if (!empty($user)) {
            // we can authenticate only local users
            if ($user['provider'] != ProviderInterface::PROVIDER_SYSTEM) {
                return null;
            }

            // check password
            if (password_verify($password, $user['password'])) {
                return $user['id'];
            } else {
                $this->eventDispatcher->dispatch(new FailedAuthenticationEvent(UserContext::newContext($user['id'])));
            }
        }

        return null;
    }

    public function create(User_Create $user, UserContext $context)
    {
        // check whether user name exists
        if ($this->userTable->getCount(new Condition(['name', '=', $user->getName()])) > 0) {
            throw new StatusCode\BadRequestException('User name already exists');
        }

        // check whether user email exists
        if ($this->userTable->getCount(new Condition(['email', '=', $user->getEmail()])) > 0) {
            throw new StatusCode\BadRequestException('User email already exists');
        }

        // check values
        User\Validator::assertName($user->getName());
        User\Validator::assertEmail($user->getEmail());
        User\Validator::assertPassword($user->getPassword(), $this->configService->getValue('user_pw_length'));

        try {
            $this->userTable->beginTransaction();

            // create user
            $record = [
                'role_id'  => $user->getRoleId(),
                'provider' => ProviderInterface::PROVIDER_SYSTEM,
                'status'   => $user->getStatus(),
                'name'     => $user->getName(),
                'email'    => $user->getEmail(),
                'password' => $user->getPassword() !== null ? \password_hash($user->getPassword(), PASSWORD_DEFAULT) : null,
                'points'   => $this->configService->getValue('points_default') ?: null,
                'date'     => new DateTime(),
            ];

            $this->userTable->create($record);

            $userId = $this->userTable->getLastInsertId();
            $user->setId($userId);

            // add scopes
            $this->insertScopesByRole($userId, $user->getRoleId());

            $this->userTable->commit();
        } catch (\Throwable $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($user, $context));

        return $userId;
    }

    public function createRemote(User_Remote $remote, UserContext $context)
    {
        // check whether user exists
        $condition  = new Condition();
        $condition->equals('provider', $remote->getProvider());
        $condition->equals('remote_id', $remote->getRemoteId());

        $existing = $this->userTable->getOneBy($condition);
        if (!empty($existing)) {
            return $existing['id'];
        }

        // replace spaces with a dot
        $remote->setName(str_replace(' ', '.', $remote->getName()));

        // check values
        User\Validator::assertName($remote->getName());

        if (!empty($remote->getEmail())) {
            User\Validator::assertEmail($remote->getEmail());
        }

        try {
            $this->userTable->beginTransaction();

            $roleId = (int) $this->configService->getValue('role_default');

            // create user
            $record = [
                'role_id'   => $roleId,
                'provider'  => $remote->getProvider(),
                'status'    => Table\User::STATUS_ACTIVE,
                'remote_id' => $remote->getRemoteId(),
                'name'      => $remote->getName(),
                'email'     => $remote->getEmail(),
                'password'  => null,
                'points'    => $this->configService->getValue('points_default') ?: null,
                'date'      => new DateTime(),
            ];

            $this->userTable->create($record);

            $userId = $this->userTable->getLastInsertId();

            // add scopes
            $this->insertScopesByRole($userId, $roleId);

            $this->userTable->commit();
        } catch (\Throwable $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        $user = new User_Create();
        $user->setId($userId);
        $user->setName($remote->getName());
        $user->setEmail($remote->getEmail());

        $this->eventDispatcher->dispatch(new CreatedEvent($user, $context));

        return $userId;
    }

    public function update(int $userId, User_Update $user, UserContext $context)
    {
        $existing = $this->userTable->get($userId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if ($user->getStatus() === null) {
            $user->setStatus($existing['status']);
        }

        if ($user->getName() === null) {
            $user->setName($existing['name']);
        }

        // check values
        User\Validator::assertName($user->getName());
        User\Validator::assertEmail($user->getEmail());

        try {
            $this->userTable->beginTransaction();

            // update user
            $record = [
                'id'      => $existing['id'],
                'role_id' => $user->getRoleId(),
                'status'  => $user->getStatus(),
                'name'    => $user->getName(),
                'email'   => $user->getEmail(),
            ];

            $this->userTable->update($record);

            if ($user->getScopes() !== null) {
                // delete existing scopes
                $this->userScopeTable->deleteAllFromUser($existing['id']);

                // add scopes
                $this->insertScopes($existing['id'], $user->getScopes());
            }

            // update attributes
            $this->updateAttributes($existing['id'], $user->getAttributes());

            $this->userTable->commit();
        } catch (\Throwable $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($user, $existing, $context));
    }

    public function delete($userId, UserContext $context)
    {
        $existing = $this->userTable->get($userId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        $record = [
            'id'     => $existing['id'],
            'status' => Table\User::STATUS_DELETED,
        ];

        $this->userTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }

    public function changeStatus($userId, $status, UserContext $context)
    {
        $user = $this->userTable->get($userId);

        if (empty($user)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        $record = [
            'id'     => $user['id'],
            'status' => $status,
        ];

        $this->userTable->update($record);

        $this->eventDispatcher->dispatch(new ChangedStatusEvent($userId, $user['status'], $status, $context));
    }

    public function changePassword(Account_ChangePassword $changePassword, UserContext $context)
    {
        $appId  = $context->getAppId();
        $userId = $context->getUserId();

        // we can only change the password through the backend app
        if (!in_array($appId, [1, 2])) {
            throw new StatusCode\BadRequestException('Changing the password is only possible through the backend or consumer app');
        }

        if (empty($changePassword->getNewPassword())) {
            throw new StatusCode\BadRequestException('New password must not be empty');
        }

        if (empty($changePassword->getOldPassword())) {
            throw new StatusCode\BadRequestException('Old password must not be empty');
        }

        // check verify password
        if ($changePassword->getNewPassword() != $changePassword->getVerifyPassword()) {
            throw new StatusCode\BadRequestException('New password does not match the verify password');
        }

        // assert password complexity
        User\Validator::assertPassword($changePassword->getNewPassword(), $this->configService->getValue('user_pw_length'));

        // change password
        $result = $this->userTable->changePassword($userId, $changePassword->getOldPassword(), $changePassword->getNewPassword());

        if ($result) {
            $this->eventDispatcher->dispatch(new ChangedPasswordEvent($changePassword, $context));

            return true;
        } else {
            throw new StatusCode\BadRequestException('Changing password failed');
        }
    }

    public function getValidScopes($userId, array $scopes)
    {
        return Table\Scope::getNames($this->userScopeTable->getValidScopes($userId, $scopes));
    }

    public function getAvailableScopes($userId)
    {
        return Table\Scope::getNames($this->userScopeTable->getAvailableScopes($userId));
    }

    protected function insertScopes(int $userId, array $scopes)
    {
        $scopes = $this->scopeTable->getValidScopes($scopes);

        foreach ($scopes as $scope) {
            $this->userScopeTable->create(array(
                'user_id'  => $userId,
                'scope_id' => $scope['id'],
            ));
        }
    }

    protected function insertScopesByRole(int $userId, int $roleId)
    {
        $scopes = $this->roleScopeTable->getAvailableScopes($roleId);
        if (!empty($scopes)) {
            foreach ($scopes as $scope) {
                $this->userScopeTable->create(array(
                    'user_id'  => $userId,
                    'scope_id' => $scope['id'],
                ));
            }
        }
    }

    protected function updateAttributes($userId, $attributes)
    {
        if (empty($this->userAttributes)) {
            // in case we have no attributes defined
            return;
        }

        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                if (in_array($name, $this->userAttributes)) {
                    $this->userTable->setAttribute($userId, $name, $value);
                }
            }
        }
    }
}
