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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Role_Create;
use Fusio\Model\Backend\Role_Update;
use Fusio\Impl\Event\Role\CreatedEvent;
use Fusio\Impl\Event\Role\DeletedEvent;
use Fusio\Impl\Event\Role\UpdatedEvent;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function create(Role_Create $role, UserContext $context): int
    {
        // check whether role exists
        if ($this->exists($role->getName())) {
            throw new StatusCode\BadRequestException('Role already exists');
        }

        try {
            $this->roleTable->beginTransaction();

            // create role
            $record = new Table\Generated\RoleRow([
                'category_id' => $role->getCategoryId(),
                'status'      => Table\Role::STATUS_ACTIVE,
                'name'        => $role->getName(),
            ]);

            $this->roleTable->create($record);

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

    public function update(int $roleId, Role_Update $role, UserContext $context): int
    {
        $existing = $this->roleTable->find($roleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find role');
        }

        if ($existing['status'] == Table\Role::STATUS_DELETED) {
            throw new StatusCode\GoneException('Role was deleted');
        }

        try {
            $this->roleTable->beginTransaction();

            // update role
            $record = new Table\Generated\RoleRow([
                'id'          => $existing['id'],
                'category_id' => $role->getCategoryId(),
                'name'        => $role->getName(),
            ]);

            $this->roleTable->update($record);

            if ($role->getScopes() !== null) {
                // delete existing scopes
                $this->roleScopeTable->deleteAllFromRole($existing['id']);

                // add scopes
                $this->insertScopes($existing['id'], $role->getScopes());
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

        $record = new Table\Generated\RoleRow([
            'id'     => $existing['id'],
            'status' => Table\Role::STATUS_DELETED,
        ]);

        $this->roleTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $roleId;
    }

    public function exists(string $name): int|false
    {
        $condition  = new Condition();
        $condition->notEquals('status', Table\Role::STATUS_DELETED);
        $condition->equals('name', $name);

        $role = $this->roleTable->findOneBy($condition);

        if (!empty($role)) {
            return $role['id'];
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
                    'role_id'  => $roleId,
                    'scope_id' => $scope['id'],
                ]));
            }
        }
    }
}
