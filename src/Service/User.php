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

use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\User\ChangedPasswordEvent;
use Fusio\Impl\Event\User\ChangedStatusEvent;
use Fusio\Impl\Event\User\CreatedEvent;
use Fusio\Impl\Event\User\DeletedEvent;
use Fusio\Impl\Event\User\FailedAuthenticationEvent;
use Fusio\Impl\Event\User\UpdatedEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Backend\AccountChangePassword;
use Fusio\Model\Backend\UserCreate;
use Fusio\Model\Backend\UserRemote;
use Fusio\Model\Backend\UserUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class User
{
    private Table\User $userTable;
    private Table\Scope $scopeTable;
    private Table\User\Scope $userScopeTable;
    private Table\Role\Scope $roleScopeTable;
    private Table\Role $roleTable;
    private Table\Plan $planTable;
    private Service\Config $configService;
    private User\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\User $userTable, Table\Scope $scopeTable, Table\User\Scope $userScopeTable, Table\Role\Scope $roleScopeTable, Table\Role $roleTable, Table\Plan $planTable, Service\Config $configService, User\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->userTable       = $userTable;
        $this->scopeTable      = $scopeTable;
        $this->userScopeTable  = $userScopeTable;
        $this->roleScopeTable  = $roleScopeTable;
        $this->roleTable       = $roleTable;
        $this->planTable       = $planTable;
        $this->configService   = $configService;
        $this->validator       = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(UserCreate $user, UserContext $context): int
    {
        $this->validator->assert($user);

        $roleId = $user->getRoleId();

        try {
            $this->userTable->beginTransaction();

            $password = $user->getPassword();

            $row = new Table\Generated\UserRow();
            $row->setRoleId($roleId);
            $row->setPlanId($user->getPlanId());
            $row->setProvider(ProviderInterface::PROVIDER_SYSTEM);
            $row->setStatus($user->getStatus() ?? Table\User::STATUS_DISABLED);
            $row->setName($user->getName());
            $row->setEmail($user->getEmail());
            $row->setPassword($password !== null ? \password_hash($password, PASSWORD_DEFAULT) : null);
            $row->setPoints($this->configService->getValue('points_default') ?: null);
            $row->setMetadata($user->getMetadata() !== null ? json_encode($user->getMetadata()) : null);
            $row->setDate(LocalDateTime::now());
            $this->userTable->create($row);

            $userId = $this->userTable->getLastInsertId();
            $user->setId($userId);

            // add scopes
            $this->insertScopesByRole($userId, $roleId);

            $this->userTable->commit();
        } catch (\Throwable $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($user, $context));

        return $userId;
    }

    public function createRemote(UserRemote $remote, UserContext $context): int
    {
        // check whether user exists
        $condition  = Condition::withAnd();
        $condition->equals(Table\Generated\UserTable::COLUMN_PROVIDER, $remote->getProvider());
        $condition->equals(Table\Generated\UserTable::COLUMN_REMOTE_ID, $remote->getRemoteId());

        $existing = $this->userTable->findOneBy($condition);
        if ($existing instanceof Table\Generated\UserRow) {
            return $existing->getId();
        }

        // replace spaces with a dot
        $remote->setName(str_replace(' ', '.', $remote->getName() ?? ''));

        // check values
        $this->validator->assertName($remote->getName());

        if (!empty($remote->getEmail())) {
            $this->validator->assertEmail($remote->getEmail());
        }

        try {
            $this->userTable->beginTransaction();

            $role = $this->roleTable->findOneByName($this->configService->getValue('role_default'));
            if (empty($role)) {
                throw new StatusCode\InternalServerErrorException('Invalid default role configured');
            }

            $roleId = $role->getId();

            // create user
            $row = new Table\Generated\UserRow();
            $row->setRoleId($roleId);
            $row->setProvider($remote->getProvider());
            $row->setStatus(Table\User::STATUS_ACTIVE);
            $row->setRemoteId($remote->getRemoteId());
            $row->setName($remote->getName());
            $row->setEmail($remote->getEmail());
            $row->setPassword(null);
            $row->setPoints($this->configService->getValue('points_default') ?: null);
            $row->setDate(LocalDateTime::now());
            $this->userTable->create($row);

            $userId = $this->userTable->getLastInsertId();

            // add scopes
            $this->insertScopesByRole($userId, $roleId);

            $this->userTable->commit();
        } catch (\Throwable $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        $user = new UserCreate();
        $user->setId($userId);
        $user->setName($remote->getName());
        $user->setEmail($remote->getEmail());

        $this->eventDispatcher->dispatch(new CreatedEvent($user, $context));

        return $userId;
    }

    public function update(string $userId, UserUpdate $user, UserContext $context): int
    {
        $existing = $this->userTable->findOneByIdentifier($userId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if ($existing->getStatus() == Table\User::STATUS_DELETED) {
            throw new StatusCode\GoneException('User was deleted');
        }

        $this->validator->assert($user, $existing);

        try {
            $this->userTable->beginTransaction();

            // update user
            $row = new Table\Generated\UserRow();
            $row->setId($existing->getId());
            $row->setRoleId($user->getRoleId() ?? $existing->getRoleId());
            $row->setPlanId($user->getPlanId() ?? $existing->getPlanId());
            $row->setStatus($user->getStatus() ?? $existing->getStatus());
            $row->setName($user->getName() ?? $existing->getName());
            $row->setEmail($user->getEmail() ?? $existing->getEmail());
            $row->setMetadata($user->getMetadata() !== null ? json_encode($user->getMetadata()) : null);
            $this->userTable->update($row);

            $scopes = $user->getScopes();
            if ($scopes !== null) {
                // delete existing scopes
                $this->userScopeTable->deleteAllFromUser($existing->getId());

                // add scopes
                $this->insertScopes($existing->getId(), $scopes);
            }

            $this->userTable->commit();
        } catch (\Throwable $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($user, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $userId, UserContext $context): int
    {
        $existing = $this->userTable->findOneByIdentifier($userId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if ($existing->getStatus() == Table\User::STATUS_DELETED) {
            throw new StatusCode\GoneException('User was deleted');
        }

        $existing->setStatus(Table\User::STATUS_DELETED);
        $this->userTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    public function changeStatus(int $userId, int $status, UserContext $context): void
    {
        $existing = $this->userTable->find($userId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        $oldStatus = $existing->getStatus();

        $existing->setStatus($status);
        $this->userTable->update($existing);

        $this->eventDispatcher->dispatch(new ChangedStatusEvent($userId, $oldStatus, $status, $context));
    }

    public function changePassword(AccountChangePassword $changePassword, UserContext $context): bool
    {
        $appId  = $context->getAppId();
        $userId = $context->getUserId();

        // we can only change the password through the backend app
        if (!in_array($appId, [1, 2])) {
            throw new StatusCode\BadRequestException('Changing the password is only possible through the backend or consumer app');
        }

        $newPassword = $changePassword->getNewPassword();
        if (empty($newPassword)) {
            throw new StatusCode\BadRequestException('New password must not be empty');
        }

        $oldPassword = $changePassword->getOldPassword();
        if (empty($oldPassword)) {
            throw new StatusCode\BadRequestException('Old password must not be empty');
        }

        // check verify password
        if ($newPassword != $changePassword->getVerifyPassword()) {
            throw new StatusCode\BadRequestException('New password does not match the verify password');
        }

        // assert password complexity
        $this->validator->assertPassword($newPassword);

        // change password
        $result = $this->userTable->changePassword($userId, $oldPassword, $newPassword);

        if ($result) {
            $this->eventDispatcher->dispatch(new ChangedPasswordEvent($changePassword, $context));

            return true;
        } else {
            throw new StatusCode\BadRequestException('Changing password failed');
        }
    }

    public function getAvailableScopes(int $userId): array
    {
        return Table\Scope::getNames($this->userScopeTable->getAvailableScopes($userId));
    }

    private function insertScopes(int $userId, array $scopes): void
    {
        $scopes = $this->scopeTable->getValidScopes($scopes);
        foreach ($scopes as $scope) {
            $row = new Table\Generated\UserScopeRow();
            $row->setUserId($userId);
            $row->setScopeId($scope->getId());
            $this->userScopeTable->create($row);
        }
    }

    private function insertScopesByRole(int $userId, int $roleId): void
    {
        $scopes = $this->roleScopeTable->getAvailableScopes($roleId);
        if (!empty($scopes)) {
            foreach ($scopes as $scope) {
                $row = new Table\Generated\UserScopeRow();
                $row->setUserId($userId);
                $row->setScopeId($scope['id']);
                $this->userScopeTable->create($row);
            }
        }
    }
}
