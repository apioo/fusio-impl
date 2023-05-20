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
use Fusio\Impl\Event\Plan\CreatedEvent;
use Fusio\Impl\Event\Plan\DeletedEvent;
use Fusio\Impl\Event\Plan\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\PlanCreate;
use Fusio\Model\Backend\PlanUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Plan
{
    private Table\Plan $planTable;
    private Table\Scope $scopeTable;
    private Table\Plan\Scope $planScopeTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Plan $planTable, Table\Scope $scopeTable, Table\Plan\Scope $planScopeTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->planTable       = $planTable;
        $this->scopeTable      = $scopeTable;
        $this->planScopeTable  = $planScopeTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(PlanCreate $plan, UserContext $context): int
    {
        $name = $plan->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        // check whether plan exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Plan already exists');
        }

        // create plan
        try {
            $this->planTable->beginTransaction();

            $price = $plan->getPrice();
            if ($price !== null) {
                $price = (int) ($price * 100);
            }

            $row = new Table\Generated\PlanRow();
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
                $this->insertScopes($planId, $scopes);
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

        if ($existing->getStatus() == Table\Event::STATUS_DELETED) {
            throw new StatusCode\GoneException('Plan was deleted');
        }

        $price = $plan->getPrice();
        if ($price !== null) {
            $price = (int) ($price * 100);
        }

        // update event
        $existing->setName($plan->getName());
        $existing->setDescription($plan->getDescription());
        $existing->setPrice($price);
        $existing->setPoints($plan->getPoints());
        $existing->setPeriodType($plan->getPeriod());
        $existing->setExternalId($plan->getExternalId());
        $existing->setMetadata($plan->getMetadata() !== null ? json_encode($plan->getMetadata()) : null);
        $this->planTable->update($existing);

        $scopes = $plan->getScopes();
        if ($scopes !== null) {
            // delete existing scopes
            $this->planScopeTable->deleteAllFromPlan($existing->getId());

            // add scopes
            $this->insertScopes($existing->getId(), $scopes);
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

        $existing->setStatus(Table\Rate::STATUS_DELETED);
        $this->planTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
    
    public function exists(string $name): int|false
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\PlanTable::COLUMN_STATUS, Table\Event::STATUS_ACTIVE);
        $condition->equals(Table\Generated\PlanTable::COLUMN_NAME, $name);

        $plan = $this->planTable->findOneBy($condition);

        if ($plan instanceof Table\Generated\PlanRow) {
            return $plan->getId();
        } else {
            return false;
        }
    }

    private function insertScopes(int $planId, array $scopes): void
    {
        $scopes = $this->scopeTable->getValidScopes($scopes);

        foreach ($scopes as $scope) {
            $row = new Table\Generated\PlanScopeRow();
            $row->setPlanId($planId);
            $row->setScopeId($scope->getId());
            $this->planScopeTable->create($row);
        }
    }
}
