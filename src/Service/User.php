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

use Fusio\Engine\Identity\UserInfo;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\User\ChangedPasswordEvent;
use Fusio\Impl\Event\User\ChangedStatusEvent;
use Fusio\Impl\Event\User\CreatedEvent;
use Fusio\Impl\Event\User\DeletedEvent;
use Fusio\Impl\Event\User\UpdatedEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Backend\AccountChangePassword;
use Fusio\Model\Backend\UserCreate;
use Fusio\Model\Backend\UserUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class User
{
    private Table\User $userTable;
    private Table\Scope $scopeTable;
    private Table\User\Scope $userScopeTable;
    private Table\Role\Scope $roleScopeTable;
    private Table\Role $roleTable;
    private Service\Config $configService;
    private User\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\User $userTable, Table\Scope $scopeTable, Table\User\Scope $userScopeTable, Table\Role\Scope $roleScopeTable, Table\Role $roleTable, Service\Config $configService, User\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->userTable = $userTable;
        $this->scopeTable = $scopeTable;
        $this->userScopeTable = $userScopeTable;
        $this->roleScopeTable = $roleScopeTable;
        $this->roleTable = $roleTable;
        $this->configService = $configService;
        $this->validator = $validator;
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
            $row->setTenantId($context->getTenantId());
            $row->setRoleId($roleId);
            $row->setPlanId($user->getPlanId());
            $row->setIdentityId(null);
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

    public function createRemote(Table\Generated\IdentityRow $identity, UserInfo $userInfo, UserContext $context): int
    {
        // check whether user exists
        $condition  = Condition::withAnd();
        $tenantId = $context->getTenantId();
        if (!empty($tenantId)) {
            $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $tenantId);
        }
        $condition->equals(Table\Generated\UserTable::COLUMN_IDENTITY_ID, $identity->getId());
        $condition->equals(Table\Generated\UserTable::COLUMN_REMOTE_ID, $userInfo->getId());

        $existing = $this->userTable->findOneBy($condition);
        if ($existing instanceof Table\Generated\UserRow) {
            return $existing->getId();
        }

        if (!$identity->getAllowCreate()) {
            throw new StatusCode\BadRequestException('Provided user is not available');
        }

        // replace spaces with a dot
        $name = str_replace(' ', '_', $userInfo->getName());

        // check values
        $this->validator->assertName($name);

        $email = $userInfo->getEmail();
        if (!empty($email)) {
            $this->validator->assertEmail($email);
        }

        try {
            $this->userTable->beginTransaction();

            $roleId = $identity->getRoleId();
            if (!empty($roleId)) {
                $role = $this->roleTable->find($roleId);
            } else {
                $role = $this->roleTable->findOneByName($this->configService->getValue('role_default'));
            }

            if (empty($role)) {
                throw new StatusCode\InternalServerErrorException('Invalid default role configured');
            }

            $roleId = $role->getId();

            // create user
            $row = new Table\Generated\UserRow();
            $row->setRoleId($roleId);
            $row->setIdentityId($identity->getId());
            $row->setStatus(Table\User::STATUS_ACTIVE);
            $row->setRemoteId($userInfo->getId());
            $row->setName($name);
            $row->setEmail($email);
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
        $user->setName($name);
        $user->setEmail($email);

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
            $row->setTenantId($context->getTenantId());
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
        $scopes = $this->scopeTable->getValidScopes($scopes, $this->config->get('fusio_tenant_id'));
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
