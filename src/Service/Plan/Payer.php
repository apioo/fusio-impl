<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Plan;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Plan\PayedEvent;
use Fusio\Impl\Event\PlanEvents;
use Fusio\Impl\Table;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Payer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Payer
{
    /**
     * @var \Fusio\Impl\Table\User
     */
    protected $userTable;

    /**
     * @var \Fusio\Impl\Table\Plan\Usage
     */
    protected $usageTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\User $userTable
     * @param \Fusio\Impl\Table\Plan\Usage $usageTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\User $userTable, Table\Plan\Usage $usageTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->userTable       = $userTable;
        $this->usageTable      = $usageTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Method which is called in case a user visits a route which cost a
     * specific amount of points. This method decreases the points from the
     * user account
     *
     * @param integer $points
     * @param \Fusio\Engine\ContextInterface $context
     */
    public function pay($points, ContextInterface $context)
    {
        // decrease user points
        $this->userTable->payPoints($context->getUser()->getId(), $points);

        // add usage entry
        $this->usageTable->create([
            'route_id' => $context->getRouteId(),
            'user_id' => $context->getUser()->getId(),
            'app_id' => $context->getApp()->getId(),
            'points' => $points,
            'insert_date' => new \DateTime(),
        ]);

        // dispatch payed event
        $userContext = UserContext::newContext(
            $context->getUser()->getId(),
            $context->getApp()->getId()
        );

        $this->eventDispatcher->dispatch(PlanEvents::PAY, new PayedEvent($points, $userContext));
    }
}
