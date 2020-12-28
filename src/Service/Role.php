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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Role_Create;
use Fusio\Impl\Backend\Model\Role_Update;
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
 * @link    http://fusio-project.org
 */
class Role
{
    /**
     * @var \Fusio\Impl\Table\Role
     */
    private $roleTable;

    /**
     * @var \Fusio\Impl\Table\Role\Scope
     */
    private $roleScopeTable;

    /**
     * @var \Fusio\Impl\Table\Scope
     */
    private $scopeTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Role $roleTable
     * @param \Fusio\Impl\Table\Role\Scope $roleScopeTable
     * @param \Fusio\Impl\Table\Scope $scopeTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Role $roleTable, Table\Role\Scope $roleScopeTable, Table\Scope $scopeTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->roleTable       = $roleTable;
        $this->roleScopeTable  = $roleScopeTable;
        $this->scopeTable      = $scopeTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Role_Create $role, UserContext $context)
    {
        // check whether role exists
        if ($this->exists($role->getName())) {
            throw new StatusCode\BadRequestException('Role already exists');
        }

        try {
            $this->roleTable->beginTransaction();

            // create role
            $record = [
                'category_id' => $role->getCategoryId(),
                'status'      => Table\Role::STATUS_ACTIVE,
                'name'        => $role->getName(),
            ];

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

    public function update(int $roleId, Role_Update $role, UserContext $context)
    {
        $existing = $this->roleTable->get($roleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find role');
        }

        if ($existing['status'] == Table\Role::STATUS_DELETED) {
            throw new StatusCode\GoneException('Role was deleted');
        }

        try {
            $this->roleTable->beginTransaction();

            // update role
            $record = [
                'id'          => $existing['id'],
                'category_id' => $role->getCategoryId(),
                'name'        => $role->getName(),
            ];

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
    }

    public function delete($roleId, UserContext $context)
    {
        $existing = $this->roleTable->get($roleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find role');
        }

        $record = [
            'id'     => $existing['id'],
            'status' => Table\Role::STATUS_DELETED,
        ];

        $this->roleTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }

    public function exists(string $name)
    {
        $condition  = new Condition();
        $condition->notEquals('status', Table\Role::STATUS_DELETED);
        $condition->equals('name', $name);

        $role = $this->roleTable->getOneBy($condition);

        if (!empty($role)) {
            return $role['id'];
        } else {
            return false;
        }
    }

    protected function insertScopes(int $roleId, array $scopes)
    {
        if (!empty($scopes) && is_array($scopes)) {
            $scopes = $this->scopeTable->getValidScopes($scopes);

            foreach ($scopes as $scope) {
                $this->roleScopeTable->create(array(
                    'role_id'  => $roleId,
                    'scope_id' => $scope['id'],
                ));
            }
        }
    }
}
