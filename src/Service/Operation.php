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

use Fusio\Impl\Action\Scheme as ActionScheme;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Operation\CreatedEvent;
use Fusio\Impl\Event\Operation\DeletedEvent;
use Fusio\Impl\Event\Operation\UpdatedEvent;
use Fusio\Impl\Framework\Schema\Scheme as SchemaScheme;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Backend\OperationCreate;
use Fusio\Model\Backend\OperationParameters;
use Fusio\Model\Backend\OperationThrows;
use Fusio\Model\Backend\OperationUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Api\OperationInterface;
use PSX\Framework\Loader\RoutingParser\InvalidateableInterface;
use PSX\Framework\Loader\RoutingParserInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Operation
{
    public function __construct(
        private Table\Operation $operationTable,
        private Table\Action $actionTable,
        private Table\ActionCommit $actionCommitTable,
        private Table\Schema $schemaTable,
        private Table\SchemaCommit $schemaCommitTable,
        private Service\Operation\Validator $validator,
        private Service\Scope $scopeService,
        private RoutingParserInterface $routingParser,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(OperationCreate $operation, UserContext $context): int
    {
        $this->validator->assert($operation, $context->getCategoryId(), $context->getTenantId());

        // create operation
        try {
            $this->operationTable->beginTransaction();

            $row = new Table\Generated\OperationRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Operation::STATUS_ACTIVE);
            $row->setActive($operation->getActive() !== null ? (int) $operation->getActive() : 1);
            $row->setPublic($operation->getPublic() !== null ? (int) $operation->getPublic() : 0);
            $row->setStability($operation->getStability());
            $row->setDescription($operation->getDescription());
            $row->setHttpMethod($operation->getHttpMethod());
            $row->setHttpPath($operation->getHttpPath());
            $row->setHttpCode($operation->getHttpCode());
            $row->setName($operation->getName());
            $row->setParameters($this->wrapParameters($operation->getParameters()));
            $row->setIncoming(SchemaScheme::wrap($operation->getIncoming()));
            $row->setOutgoing(SchemaScheme::wrap($operation->getOutgoing()));
            $row->setThrows($this->wrapThrows($operation->getThrows(), $context));
            $row->setAction(ActionScheme::wrap($operation->getAction()));
            $row->setCosts($operation->getCosts());
            $row->setMetadata($operation->getMetadata() !== null ? Parser::encode($operation->getMetadata()) : null);
            $this->operationTable->create($row);

            $operationId = $this->operationTable->getLastInsertId();
            $operation->setId($operationId);

            // assign scopes
            $scopes = $operation->getScopes();
            if (!empty($scopes)) {
                $this->scopeService->createForOperation($operationId, $scopes, $context);
            }

            $this->operationTable->commit();
        } catch (\Throwable $e) {
            $this->operationTable->rollBack();

            throw $e;
        }

        if ($this->routingParser instanceof InvalidateableInterface) {
            $this->routingParser->invalidate();
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($operation, $context));

        return $operationId;
    }

    public function update(string $operationId, OperationUpdate $operation, UserContext $context): int
    {
        $existing = $this->operationTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $operationId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find operation');
        }

        if ($existing->getStatus() == Table\Operation::STATUS_DELETED) {
            throw new StatusCode\GoneException('Operation was deleted');
        }

        $this->validator->assert($operation, $context->getCategoryId(), $context->getTenantId(), $existing);

        $isStable = in_array($existing->getStability(), [OperationInterface::STABILITY_STABLE, OperationInterface::STABILITY_LEGACY], true);

        $action = $operation->getAction();
        if (!empty($action)) {
            $action = $this->fixActionToCurrentCommitHash($action, $context);
        }

        $incoming = $existing->getIncoming();
        if (!empty($incoming)) {
            $incoming = $this->fixSchemaToCurrentCommitHash($incoming, $context);
        }

        $outgoing = $existing->getOutgoing();
        if (!empty($outgoing)) {
            $outgoing = $this->fixSchemaToCurrentCommitHash($outgoing, $context);
        }

        try {
            $this->operationTable->beginTransaction();

            // update operation
            if ($isStable) {
                // if the operation is stable or legacy we can only change the stability
                $existing->setStability($operation->getStability());
            } else {
                $existing->setActive($operation->getActive() !== null ? (int) $operation->getActive() : $existing->getActive());
                $existing->setPublic($operation->getPublic() !== null ? (int) $operation->getPublic() : $existing->getPublic());
                $existing->setStability($operation->getStability() ?? $existing->getStability());
                $existing->setDescription($operation->getDescription() ?? $existing->getDescription());
                $existing->setHttpMethod($operation->getHttpMethod() ?? $existing->getHttpMethod());
                $existing->setHttpPath($operation->getHttpPath() ?? $existing->getHttpPath());
                $existing->setHttpCode($operation->getHttpCode() ?? $existing->getHttpCode());
                $existing->setName($operation->getName() ?? $existing->getName());
                $parameters = $operation->getParameters();
                if ($parameters !== null) {
                    $existing->setParameters($this->wrapParameters($parameters));
                }
                if (in_array($existing->getHttpMethod(), ['POST', 'PUT', 'PATCH'], true)) {
                    $existing->setIncoming(SchemaScheme::wrap($incoming ?? $existing->getIncoming()));
                } else {
                    $existing->setIncoming(null);
                }
                $existing->setOutgoing(SchemaScheme::wrap($outgoing ?? $existing->getOutgoing()));
                $throws = $operation->getThrows();
                if ($throws !== null) {
                    $existing->setThrows($this->wrapThrows($throws, $context));
                }
                $existing->setAction(ActionScheme::wrap($action ?? $existing->getAction()));
                $existing->setCosts($operation->getCosts() ?? $existing->getCosts());
                $metadata = $operation->getMetadata();
                if ($metadata !== null) {
                    $existing->setMetadata(Parser::encode($metadata));
                }
            }

            $this->operationTable->update($existing);

            if (!$isStable) {
                // assign scopes
                $scopes = $operation->getScopes();
                if (!empty($scopes)) {
                    $this->scopeService->createForOperation($existing->getId(), $scopes, $context);
                }
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
        $existing = $this->operationTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $operationId);
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

    private function wrapParameters(?OperationParameters $parameters): ?string
    {
        if ($parameters === null) {
            return null;
        }

        return Parser::encode($parameters);
    }

    private function wrapThrows(?OperationThrows $throws, UserContext $context): ?string
    {
        if ($throws === null) {
            return null;
        }

        foreach ($throws->getAll() as $code => $schema) {
            if (!empty($schema)) {
                $schema = $this->fixSchemaToCurrentCommitHash($schema, $context);
            }

            $throws->put($code, SchemaScheme::wrap($schema));
        }

        return Parser::encode($throws);
    }

    private function fixSchemaToCurrentCommitHash(?string $schema, UserContext $context): ?string
    {
        $scheme = SchemaScheme::wrap($schema);
        if (empty($scheme)) {
            return $schema;
        }

        if (!str_starts_with($scheme, 'schema://')) {
            return $schema;
        }

        $row = $this->schemaTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), substr($scheme, 9));
        if (!$row instanceof Table\Generated\SchemaRow) {
            return $schema;
        }

        $schemaHash = $this->schemaCommitTable->findCurrentHash($row->getId());
        if (empty($schemaHash)) {
            return $schema;
        }

        return $schema . '@' . $schemaHash;
    }

    private function fixActionToCurrentCommitHash(?string $action, UserContext $context): ?string
    {
        $scheme = ActionScheme::wrap($action);
        if (empty($scheme)) {
            return $action;
        }

        if (!str_starts_with($scheme, 'action://')) {
            return $action;
        }

        $row = $this->actionTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), substr($scheme, 9));
        if (!$row instanceof Table\Generated\ActionRow) {
            return $action;
        }

        $actionHash = $this->actionCommitTable->findCurrentHash($row->getId());
        if (empty($actionHash)) {
            return $action;
        }

        return $action . '@' . $actionHash;
    }
}
