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
use Fusio\Impl\Backend\Model\Plan_Create;
use Fusio\Impl\Backend\Model\Plan_Update;
use Fusio\Impl\Event\Plan\CreatedEvent;
use Fusio\Impl\Event\Plan\DeletedEvent;
use Fusio\Impl\Event\Plan\UpdatedEvent;
use Fusio\Impl\Event\PlanEvents;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Plan
{
    /**
     * @var \Fusio\Impl\Table\Plan
     */
    private $planTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Plan $planTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Plan $planTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->planTable       = $planTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Plan_Create $plan, UserContext $context)
    {
        // check whether plan exists
        if ($this->exists($plan->getName())) {
            throw new StatusCode\BadRequestException('Plan already exists');
        }

        // create event
        $record = [
            'status'      => Table\Plan::STATUS_ACTIVE,
            'name'        => $plan->getName(),
            'description' => $plan->getDescription(),
            'price'       => $plan->getPrice(),
            'points'      => $plan->getPoints(),
            'period_type' => $plan->getPeriod(),
        ];

        $this->planTable->create($record);

        // get last insert id
        $planId = $this->planTable->getLastInsertId();
        $plan->setId($planId);

        $this->eventDispatcher->dispatch(new CreatedEvent($plan, $context));

        return $planId;
    }

    public function update(int $planId, Plan_Update $plan, UserContext $context)
    {
        $existing = $this->planTable->get($planId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find plan');
        }

        if ($existing['status'] == Table\Event::STATUS_DELETED) {
            throw new StatusCode\GoneException('Plan was deleted');
        }

        // update event
        $record = [
            'id'          => $existing['id'],
            'name'        => $plan->getName(),
            'description' => $plan->getDescription(),
            'price'       => $plan->getPrice(),
            'points'      => $plan->getPoints(),
            'period_type' => $plan->getPeriod(),
        ];

        $this->planTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($plan, $existing, $context));
    }

    public function delete(int $planId, UserContext $context)
    {
        $existing = $this->planTable->get($planId);

        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find plan');
        }

        $record = [
            'id'     => $existing['id'],
            'status' => Table\Rate::STATUS_DELETED,
        ];

        $this->planTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }
    
    public function exists(string $name)
    {
        $condition  = new Condition();
        $condition->equals('status', Table\Event::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $plan = $this->planTable->getOneBy($condition);

        if (!empty($plan)) {
            return $plan['id'];
        } else {
            return false;
        }
    }
}
