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

namespace Fusio\Impl\Event\Event\Subscription;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Event_Subscription_Create;
use Fusio\Impl\Event\EventAbstract;

/**
 * CreatedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CreatedEvent extends EventAbstract
{
    /**
     * @var Event_Subscription_Create
     */
    private $subscription;

    /**
     * @param Event_Subscription_Create $subscription
     * @param UserContext $context
     */
    public function __construct(Event_Subscription_Create $subscription, UserContext $context)
    {
        parent::__construct($context);

        $this->subscription = $subscription;
    }

    /**
     * @return Event_Subscription_Create
     */
    public function getSubscription(): Event_Subscription_Create
    {
        return $this->subscription;
    }
}
