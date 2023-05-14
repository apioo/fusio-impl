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
use Fusio\Impl\Event\Operation\CreatedEvent;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\OperationCreate;
use Fusio\Model\Backend\OperationUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
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
    private Service\Scope $scopeService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Operation $operationTable, Service\Scope $scopeService, EventDispatcherInterface $eventDispatcher)
    {
        $this->operationTable  = $operationTable;
        $this->scopeService    = $scopeService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, OperationCreate $operation, UserContext $context): int
    {
        $path = $operation->getPath();
        if (empty($path)) {
            throw new StatusCode\BadRequestException('Path not provided');
        }

        Route\Validator::assertPath($path);

        $config = $operation->getConfig();
        if (empty($config)) {
            throw new StatusCode\BadRequestException('Config not provided');
        }

        // check whether route exists
        if ($this->exists($path)) {
            throw new StatusCode\BadRequestException('Route already exists');
        }

        if ($operation->getPriority() === null) {
            $operation->setPriority($this->operationTable->getMaxPriority() + 1);
        } elseif ($operation->getPriority()>= 0x1000000) {
            throw new StatusCode\GoneException('Priority can not be greater or equal to ' . 0x1000000);
        }

        $controller = $operation->getController();
        if (!empty($controller)) {
            if (!class_exists($controller)) {
                throw new StatusCode\BadRequestException('Provided controller does not exist');
            }
        } else {
            $controller = SchemaApiController::class;
        }

        // create route
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
                $this->scopeService->createFromRoute($categoryId, $operationId, $scopes, $context);
            }

            $this->operationTable->commit();
        } catch (\Throwable $e) {
            $this->operationTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($operation, $context));

        return $operationId;
    }

    public function update(int $operationId, OperationUpdate $operation, UserContext $context): int
    {
        $existing = $this->operationTable->find($operationId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find route');
        }

        if ($existing->getStatus() == Table\Operation::STATUS_DELETED) {
            throw new StatusCode\GoneException('Route was deleted');
        }

        $config = $operation->getConfig();
        if (empty($config)) {
            throw new StatusCode\BadRequestException('Config not provided');
        }

        $priority = $operation->getPriority();
        if ($priority === null) {
            $priority = $this->operationTable->getMaxPriority() + 1;
        } elseif ($priority >= 0x1000000) {
            throw new StatusCode\GoneException('Priority can not be greater or equal to ' . 0x1000000);
        }

        try {
            $this->operationTable->beginTransaction();

            // update route
            $record = new Table\Generated\RoutesRow([
                Table\Generated\RoutesTable::COLUMN_ID => $existing->getId(),
                Table\Generated\RoutesTable::COLUMN_PRIORITY => $priority,
                Table\Generated\RoutesTable::COLUMN_METADATA => $operation->getMetadata() !== null ? json_encode($operation->getMetadata()) : null,
            ]);

            $this->operationTable->update($record);

            // assign scopes
            $scopes = $operation->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createFromRoute($existing->getCategoryId(), $existing->getId(), $scopes, $context);
            }

            // handle config
            $this->configService->handleConfig($existing->getCategoryId(), $existing->getId(), $existing->getPath(), $config, $context);

            $this->operationTable->commit();
        } catch (\Throwable $e) {
            $this->operationTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($operation, $existing, $context));

        return $operationId;
    }

    public function delete(int $routeId, UserContext $context): int
    {
        $existing = $this->operationTable->find($routeId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find route');
        }

        if ($existing->getStatus() == Table\Operation::STATUS_DELETED) {
            throw new StatusCode\GoneException('Route was deleted');
        }

        // check whether route has a production version
        if ($this->methodTable->hasProductionVersion($existing->getId())) {
            throw new StatusCode\ConflictException('It is not possible to delete a route which contains an active production or deprecated method');
        }

        // delete route
        $record = new Table\Generated\RoutesRow([
            Table\Generated\RoutesTable::COLUMN_ID => $existing->getId(),
            Table\Generated\RoutesTable::COLUMN_STATUS => Table\Operation::STATUS_DELETED
        ]);

        $this->operationTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $routeId;
    }

    /**
     * Checks whether the provided path already exists
     */
    public function exists(string $path): int|false
    {
        $condition  = new Condition();
        $condition->equals(Table\Generated\RoutesTable::COLUMN_STATUS, Table\Operation::STATUS_ACTIVE);
        $condition->equals(Table\Generated\RoutesTable::COLUMN_PATH, $path);

        $route = $this->operationTable->findOneBy($condition);

        if ($route instanceof Table\Generated\RoutesRow) {
            return $route->getId();
        } else {
            return false;
        }
    }
}
