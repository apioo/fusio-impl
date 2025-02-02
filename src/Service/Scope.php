<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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
use Fusio\Impl\Event\Scope\CreatedEvent;
use Fusio\Impl\Event\Scope\DeletedEvent;
use Fusio\Impl\Event\Scope\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ScopeCreate;
use Fusio\Model\Backend\ScopeOperation;
use Fusio\Model\Backend\ScopeUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope
{
    private Table\Scope $scopeTable;
    private Table\Scope\Operation $scopeOperationTable;
    private Table\App\Scope $appScopeTable;
    private Table\User\Scope $userScopeTable;
    private Table\Operation $operationTable;
    private Scope\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Scope $scopeTable, Table\Scope\Operation $scopeOperationTable, Table\App\Scope $appScopeTable, Table\User\Scope $userScopeTable, Table\Operation $operationTable, Scope\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->scopeTable = $scopeTable;
        $this->scopeOperationTable = $scopeOperationTable;
        $this->appScopeTable = $appScopeTable;
        $this->userScopeTable = $userScopeTable;
        $this->operationTable = $operationTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(ScopeCreate $scope, UserContext $context): int
    {
        $this->validator->assert($scope, $context->getTenantId());

        try {
            $this->scopeTable->beginTransaction();

            $row = new Table\Generated\ScopeRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Scope::STATUS_ACTIVE);
            $row->setName($scope->getName());
            $row->setDescription($scope->getDescription() ?? '');
            $row->setMetadata($scope->getMetadata() !== null ? json_encode($scope->getMetadata()) : null);
            $this->scopeTable->create($row);

            $scopeId = $this->scopeTable->getLastInsertId();
            $scope->setId($scopeId);

            $this->insertOperations($scopeId, $scope->getOperations() ?? [], $context);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($scope, $context));

        return $scopeId;
    }

    public function createForOperation(int $operationId, array $scopeNames, UserContext $context): void
    {
        // remove all scopes from this route
        $this->scopeOperationTable->deleteAllFromOperation($operationId);

        // insert new scopes
        foreach ($scopeNames as $scopeName) {
            $scope = $this->scopeTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), $scopeName);
            if ($scope instanceof Table\Generated\ScopeRow) {
                // assign scope to operation
                $row = new Table\Generated\ScopeOperationRow();
                $row->setScopeId($scope->getId());
                $row->setOperationId($operationId);
                $row->setAllow(1);
                $this->scopeOperationTable->create($row);
            } else {
                // create new scope
                $operation = new ScopeOperation();
                $operation->setOperationId($operationId);
                $operation->setAllow(true);

                $scope = new ScopeCreate();
                $scope->setName($scopeName);
                $scope->setOperations([$operation]);
                $this->create($scope, $context);
            }
        }
    }

    public function update(string $scopeId, ScopeUpdate $scope, UserContext $context): int
    {
        $existing = $this->scopeTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $scopeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find scope');
        }

        if ($existing->getStatus() == Table\Scope::STATUS_DELETED) {
            throw new StatusCode\GoneException('Scope was deleted');
        }

        $this->validator->assert($scope, $context->getTenantId(), $existing);

        try {
            $this->scopeTable->beginTransaction();

            $existing->setName($scope->getName());
            $existing->setDescription($scope->getDescription() ?? '');
            $existing->setMetadata($scope->getMetadata() !== null ? json_encode($scope->getMetadata()) : null);
            $this->scopeTable->update($existing);

            $this->scopeOperationTable->deleteAllFromScope($existing->getId());

            $this->insertOperations($existing->getId(), $scope->getOperations() ?? [], $context);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($scope, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $scopeId, UserContext $context): int
    {
        $existing = $this->scopeTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $scopeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find scope');
        }

        if ($existing->getStatus() == Table\Scope::STATUS_DELETED) {
            throw new StatusCode\GoneException('Scope was deleted');
        }

        // check whether the scope is used by an app or user
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppScopeTable::COLUMN_SCOPE_ID, $existing->getId());
        $appScopes = $this->appScopeTable->getCount($condition);
        if ($appScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assigned to an app. Remove the scope from the app in order to delete the scope');
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\UserScopeTable::COLUMN_SCOPE_ID, $existing->getId());
        $userScopes = $this->userScopeTable->getCount($condition);
        if ($userScopes > 0) {
            throw new StatusCode\ConflictException('Scope is assigned to an user. Remove the scope from the user in order to delete the scope');
        }

        // check whether this is a system scope
        $systemScopes = ['default', 'backend', 'consumer', 'system', 'authorization'];
        if (in_array($existing->getName(), $systemScopes)) {
            throw new StatusCode\BadRequestException('It is not possible to delete one of the system scopes: ' . implode(', ', $systemScopes));
        }

        try {
            $this->scopeTable->beginTransaction();

            $existing->setStatus(Table\Scope::STATUS_DELETED);
            $this->scopeTable->update($existing);

            $this->scopeTable->commit();
        } catch (\Throwable $e) {
            $this->scopeTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    /**
     * Returns all scope names which are valid for the app and the user. The scopes are a comma separated list
     */
    public function getValidScopes(?string $tenantId, string $scopes, ?int $appId, ?int $userId): array
    {
        $scopes = self::split($scopes);

        if ($appId !== null) {
            $scopes = Table\Scope::getNames($this->appScopeTable->getValidScopes($tenantId, $appId, $scopes));
        }

        if ($userId !== null) {
            $scopes = Table\Scope::getNames($this->userScopeTable->getValidScopes($tenantId, $userId, $scopes));
        }

        return $scopes;
    }

    /**
     * @param ScopeOperation[] $operations
     */
    protected function insertOperations(int $scopeId, ?array $operations, UserContext $context): void
    {
        if (!empty($operations)) {
            foreach ($operations as $scopeOperation) {
                if ($scopeOperation->getAllow()) {
                    $operation = $this->operationTable->findOneByTenantAndId($context->getTenantId(), $context->getCategoryId(), $scopeOperation->getOperationId());
                    if (!$operation instanceof Table\Generated\OperationRow) {
                        throw new StatusCode\BadRequestException('Could not find provided operation id: ' . $scopeOperation->getOperationId());
                    }

                    $row = new Table\Generated\ScopeOperationRow();
                    $row->setScopeId($scopeId);
                    $row->setOperationId($operation->getId());
                    $row->setAllow(1);
                    $this->scopeOperationTable->create($row);
                }
            }
        }
    }

    public static function split(string $scopes): array
    {
        if (str_contains($scopes, ',')) {
            return explode(',', $scopes);
        } else {
            return explode(' ', $scopes);
        }
    }
}
