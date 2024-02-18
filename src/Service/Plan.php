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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Plan\CreatedEvent;
use Fusio\Impl\Event\Plan\DeletedEvent;
use Fusio\Impl\Event\Plan\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\PlanCreate;
use Fusio\Model\Backend\PlanUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Plan
{
    private Table\Plan $planTable;
    private Table\Scope $scopeTable;
    private Table\Plan\Scope $planScopeTable;
    private Plan\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Plan $planTable, Table\Scope $scopeTable, Table\Plan\Scope $planScopeTable, Plan\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->planTable = $planTable;
        $this->scopeTable = $scopeTable;
        $this->planScopeTable = $planScopeTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(PlanCreate $plan, UserContext $context): int
    {
        $this->validator->assert($plan);

        try {
            $this->planTable->beginTransaction();

            $price = $plan->getPrice();
            if ($price !== null) {
                $price = (int) ($price * 100);
            }

            $row = new Table\Generated\PlanRow();
            $row->setTenantId($context->getTenantId());
            $row->setStatus(Table\Plan::STATUS_ACTIVE);
            $row->setName($plan->getName());
            $row->setDescription($plan->getDescription());
            $row->setPrice($price);
            $row->setPoints($plan->getPoints());
            $row->setPeriodType($plan->getPeriod());
            $row->setExternalId($plan->getExternalId());
            $row->setMetadata($plan->getMetadata() !== null ? json_encode($plan->getMetadata()) : null);
            $this->planTable->create($row);

            $planId = $this->planTable->getLastInsertId();
            $plan->setId($planId);

            $scopes = $plan->getScopes();
            if ($scopes !== null) {
                // add scopes
                $this->insertScopes($planId, $scopes, $context->getTenantId());
            }

            $this->planTable->commit();
        } catch (\Throwable $e) {
            $this->planTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($plan, $context));

        return $planId;
    }

    public function update(string $planId, PlanUpdate $plan, UserContext $context): int
    {
        $existing = $this->planTable->findOneByIdentifier($planId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find plan');
        }

        if ($existing->getStatus() == Table\Plan::STATUS_DELETED) {
            throw new StatusCode\GoneException('Plan was deleted');
        }

        $this->validator->assert($plan, $existing);

        $price = $plan->getPrice();
        if ($price !== null) {
            $price = (int) ($price * 100);
        }

        $existing->setName($plan->getName() ?? $existing->getName());
        $existing->setDescription($plan->getDescription() ?? $existing->getDescription());
        $existing->setPrice($price ?? $existing->getPrice());
        $existing->setPoints($plan->getPoints() ?? $existing->getPoints());
        $existing->setPeriodType($plan->getPeriod() ?? $existing->getPeriodType());
        $existing->setExternalId($plan->getExternalId() ?? $existing->getExternalId());
        $existing->setMetadata($plan->getMetadata() !== null ? json_encode($plan->getMetadata()) : $existing->getMetadata());
        $this->planTable->update($existing);

        $scopes = $plan->getScopes();
        if ($scopes !== null) {
            // delete existing scopes
            $this->planScopeTable->deleteAllFromPlan($existing->getId());

            // add scopes
            $this->insertScopes($existing->getId(), $scopes, $context->getTenantId());
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($plan, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $planId, UserContext $context): int
    {
        $existing = $this->planTable->findOneByIdentifier($planId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find plan');
        }

        if ($existing->getStatus() == Table\Plan::STATUS_DELETED) {
            throw new StatusCode\GoneException('Plan was deleted');
        }

        $existing->setStatus(Table\Rate::STATUS_DELETED);
        $this->planTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    private function insertScopes(int $planId, array $scopes, ?string $tenantId = null): void
    {
        $scopes = $this->scopeTable->getValidScopes($scopes, $tenantId);

        foreach ($scopes as $scope) {
            $row = new Table\Generated\PlanScopeRow();
            $row->setPlanId($planId);
            $row->setScopeId($scope->getId());
            $this->planScopeTable->create($row);
        }
    }
}
