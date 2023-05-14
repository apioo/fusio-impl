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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Role\CreatedEvent;
use Fusio\Impl\Event\Role\DeletedEvent;
use Fusio\Impl\Event\Role\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\RoleCreate;
use Fusio\Model\Backend\RoleUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Role
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Role
{
    private Table\Role $roleTable;
    private Table\Role\Scope $roleScopeTable;
    private Table\Scope $scopeTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Role $roleTable, Table\Role\Scope $roleScopeTable, Table\Scope $scopeTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->roleTable       = $roleTable;
        $this->roleScopeTable  = $roleScopeTable;
        $this->scopeTable      = $scopeTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(RoleCreate $role, UserContext $context): int
    {
        $name = $role->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        // check whether role exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Role already exists');
        }

        try {
            $this->roleTable->beginTransaction();

            // create role
            $row = new Table\Generated\RoleRow();
            $row->setCategoryId($role->getCategoryId());
            $row->setStatus(Table\Role::STATUS_ACTIVE);
            $row->setName($role->getName());
            $this->roleTable->create($row);

            // get last insert id
            $roleId = $this->roleTable->getLastInsertId();
            $role->setId($roleId);

            // add scopes
            $this->insertScopes($roleId, $role->getScopes());

            $this->roleTable->commit();
        } catch (\Throwable $e) {
            $this->roleTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($role, $context));

        return $roleId;
    }

    public function update(int $roleId, RoleUpdate $role, UserContext $context): int
    {
        $existing = $this->roleTable->find($roleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find role');
        }

        if ($existing->getStatus() == Table\Role::STATUS_DELETED) {
            throw new StatusCode\GoneException('Role was deleted');
        }

        try {
            $this->roleTable->beginTransaction();

            // update role
            $existing->setCategoryId($role->getCategoryId());
            $existing->setName($role->getName());
            $this->roleTable->update($existing);

            if ($role->getScopes() !== null) {
                // delete existing scopes
                $this->roleScopeTable->deleteAllFromRole($existing->getId());

                // add scopes
                $this->insertScopes($existing->getId(), $role->getScopes());
            }

            $this->roleTable->commit();
        } catch (\Throwable $e) {
            $this->roleTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($role, $existing, $context));

        return $roleId;
    }

    public function delete(int $roleId, UserContext $context): int
    {
        $existing = $this->roleTable->find($roleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find role');
        }

        $existing->setStatus(Table\Role::STATUS_DELETED);
        $this->roleTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $roleId;
    }

    public function exists(string $name): int|false
    {
        $condition = Condition::withAnd();
        $condition->notEquals(Table\Generated\RoleTable::COLUMN_STATUS, Table\Role::STATUS_DELETED);
        $condition->equals(Table\Generated\RoleTable::COLUMN_NAME, $name);

        $role = $this->roleTable->findOneBy($condition);

        if ($role instanceof Table\Generated\RoleRow) {
            return $role->getId();
        } else {
            return false;
        }
    }

    protected function insertScopes(int $roleId, ?array $scopes): void
    {
        if (!empty($scopes)) {
            $scopes = $this->scopeTable->getValidScopes($scopes);

            foreach ($scopes as $scope) {
                $this->roleScopeTable->create(new Table\Generated\RoleScopeRow([
                    Table\Generated\RoleScopeTable::COLUMN_ROLE_ID => $roleId,
                    Table\Generated\RoleScopeTable::COLUMN_SCOPE_ID => $scope->getId(),
                ]));
            }
        }
    }
}
