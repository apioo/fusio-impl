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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Role\CreatedEvent;
use Fusio\Impl\Event\Role\DeletedEvent;
use Fusio\Impl\Event\Role\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\RoleCreate;
use Fusio\Model\Backend\RoleUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Role
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Role
{
    public function __construct(
        private Table\Role $roleTable,
        private Table\Role\Scope $roleScopeTable,
        private Table\Scope $scopeTable,
        private Table\User $userTable,
        private Table\User\Scope $userScopeTable,
        private Role\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(RoleCreate $role, UserContext $context): int
    {
        $this->validator->assert($role, $context->getTenantId());

        try {
            $this->roleTable->beginTransaction();

            // create role
            $row = new Table\Generated\RoleRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($role->getCategoryId());
            $row->setStatus(Table\Role::STATUS_ACTIVE);
            $row->setName($role->getName());
            $this->roleTable->create($row);

            // get last insert id
            $roleId = $this->roleTable->getLastInsertId();
            $role->setId($roleId);

            // add scopes
            $this->insertScopes($context->getTenantId(), $roleId, $role->getScopes());

            $this->roleTable->commit();
        } catch (\Throwable $e) {
            $this->roleTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($role, $context));

        return $roleId;
    }

    public function update(string $roleId, RoleUpdate $role, UserContext $context): int
    {
        $existing = $this->roleTable->findOneByIdentifier($context->getTenantId(), $roleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find role');
        }

        if ($existing->getStatus() == Table\Role::STATUS_DELETED) {
            throw new StatusCode\GoneException('Role was deleted');
        }

        $this->validator->assert($role, $context->getTenantId(), $existing);

        try {
            $this->roleTable->beginTransaction();

            // update role
            $existing->setCategoryId($role->getCategoryId() ?? $existing->getCategoryId());
            $existing->setName($role->getName() ?? $existing->getName());
            $this->roleTable->update($existing);

            if ($role->getScopes() !== null) {
                // delete existing scopes
                $this->roleScopeTable->deleteAllFromRole($existing->getId());

                // add scopes
                $this->insertScopes($context->getTenantId(), $existing->getId(), $role->getScopes());
            }

            $this->roleTable->commit();
        } catch (\Throwable $e) {
            $this->roleTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($role, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $roleId, UserContext $context): int
    {
        $existing = $this->roleTable->findOneByIdentifier($context->getTenantId(), $roleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find role');
        }

        if ($existing->getStatus() == Table\Role::STATUS_DELETED) {
            throw new StatusCode\GoneException('Role was deleted');
        }

        $existing->setStatus(Table\Role::STATUS_DELETED);
        $this->roleTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    private function insertScopes(?string $tenantId, int $roleId, ?array $scopes): void
    {
        if (!empty($scopes)) {
            $scopes = $this->scopeTable->getValidScopes($tenantId, $scopes);

            foreach ($scopes as $scope) {
                $row = new Table\Generated\RoleScopeRow();
                $row->setRoleId($roleId);
                $row->setScopeId($scope->getId());
                $this->roleScopeTable->create($row);
            }

            $this->addScopesToUsers($roleId, $scopes);
        }
    }

    private function addScopesToUsers(int $roleId, array $scopes): void
    {
        $users = $this->userTable->findByRoleId($roleId);
        foreach ($users as $user) {
            foreach ($scopes as $scope) {
                if ($this->userScopeTable->hasUserScope($user->getId(), $scope->getId())) {
                    continue;
                }

                $row = new Table\Generated\UserScopeRow();
                $row->setUserId($user->getId());
                $row->setScopeId($scope->getId());
                $this->userScopeTable->create($row);
            }
        }
    }
}
