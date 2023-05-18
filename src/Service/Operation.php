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

use Fusio\Model;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Operation\CreatedEvent;
use Fusio\Impl\Event\Operation\DeletedEvent;
use Fusio\Impl\Event\Operation\UpdatedEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\OperationCreate;
use Fusio\Model\Backend\OperationUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Api\OperationInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Operation
{
    private Table\Operation $operationTable;
    private Operation\Validator $validator;
    private Service\Scope $scopeService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Operation $operationTable, Service\Operation\Validator $validator, Service\Scope $scopeService, EventDispatcherInterface $eventDispatcher)
    {
        $this->operationTable  = $operationTable;
        $this->validator       = $validator;
        $this->scopeService    = $scopeService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, OperationCreate $operation, UserContext $context): int
    {
        $this->validator->assertOperation($operation);

        // check whether route exists
        if ($this->exists($operation->getName())) {
            throw new StatusCode\BadRequestException('Operation already exists');
        }

        // create operation
        try {
            $this->operationTable->beginTransaction();

            $row = new Table\Generated\OperationRow();
            $row->setCategoryId($categoryId);
            $row->setStatus(Table\Operation::STATUS_ACTIVE);
            $row->setActive($operation->getActive());
            $row->setPublic($operation->getPublic());
            $row->setStability($operation->getStability());
            $row->setDescription($operation->getDescription());
            $row->setHttpMethod($operation->getHttpMethod());
            $row->setHttpPath($operation->getHttpPath());
            $row->setHttpCode($operation->getHttpCode());
            $row->setName($operation->getName());
            $row->setParameters(\json_encode($operation->getParameters()));
            $row->setIncoming($operation->getIncoming());
            $row->setOutgoing($operation->getOutgoing());
            $row->setThrows(\json_encode($operation->getThrows()));
            $row->setAction($operation->getAction());
            $row->setCosts($operation->getCosts());
            $row->setMetadata($operation->getMetadata() !== null ? json_encode($operation->getMetadata()) : null);
            $this->operationTable->create($row);

            $operationId = $this->operationTable->getLastInsertId();
            $operation->setId($operationId);

            // assign scopes
            $scopes = $operation->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createForOperation($categoryId, $operationId, $scopes, $context);
            }

            $this->operationTable->commit();
        } catch (\Throwable $e) {
            $this->operationTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($operation, $context));

        return $operationId;
    }

    public function update(string $operationId, OperationUpdate $operation, UserContext $context): int
    {
        $existing = $this->operationTable->findOneByIdentifier($operationId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find operation');
        }

        if ($existing->getStatus() == Table\Operation::STATUS_DELETED) {
            throw new StatusCode\GoneException('Operation was deleted');
        }

        try {
            $this->operationTable->beginTransaction();

            // update operation
            if (in_array($existing->getStability(), [OperationInterface::STABILITY_STABLE, OperationInterface::STABILITY_LEGACY], true)) {
                // if the operation is stable or legacy we can only change the stability
                $existing->setStability($operation->getStability());
            } else {
                $this->validator->assertOperation($operation, $existing);

                $existing->setActive($operation->getActive());
                $existing->setPublic($operation->getPublic());
                $existing->setStability($operation->getStability());
                $existing->setDescription($operation->getDescription());
                $existing->setHttpMethod($operation->getHttpMethod());
                $existing->setHttpPath($operation->getHttpPath());
                $existing->setName($operation->getName());
                $existing->setParameters(\json_encode($operation->getParameters()));
                $existing->setIncoming($operation->getIncoming());
                $existing->setOutgoing($operation->getOutgoing());
                $existing->setThrows(\json_encode($operation->getThrows()));
                $existing->setAction($operation->getAction());
                $existing->setCosts($operation->getCosts());
                $existing->setMetadata($operation->getMetadata() !== null ? json_encode($operation->getMetadata()) : null);
            }

            $this->operationTable->update($existing);

            // assign scopes
            $scopes = $operation->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createForOperation($existing->getCategoryId(), $existing->getId(), $scopes, $context);
            }

            $this->operationTable->commit();
        } catch (\Throwable $e) {
            $this->operationTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($operation, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $operationId, UserContext $context): int
    {
        $existing = $this->operationTable->findOneByIdentifier($operationId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find operation');
        }

        if ($existing->getStatus() == Table\Operation::STATUS_DELETED) {
            throw new StatusCode\GoneException('Operation was deleted');
        }

        // check whether operation has a production version
        if ($existing->getStability() === OperationInterface::STABILITY_STABLE) {
            throw new StatusCode\ConflictException('It is not possible to delete an operation which is marked as stable');
        }

        // delete operation
        $existing->setStatus(Table\Operation::STATUS_DELETED);
        $this->operationTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    /**
     * Checks whether the provided path already exists
     */
    public function exists(string $name): int|false
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, Table\Operation::STATUS_ACTIVE);
        $condition->equals(Table\Generated\OperationTable::COLUMN_NAME, $name);

        $operation = $this->operationTable->findOneBy($condition);

        if ($operation instanceof Table\Generated\OperationRow) {
            return $operation->getId();
        } else {
            return false;
        }
    }
}
