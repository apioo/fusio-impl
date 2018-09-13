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

namespace Fusio\Impl\Event\Plan;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;

/**
 * PayedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PayedEvent extends EventAbstract
{
    /**
     * @var \Fusio\Engine\ContextInterface
     */
    protected $engineContext;

    /**
     * @var integer
     */
    protected $points;

    public function __construct(ContextInterface $engineContext, $points)
    {
        parent::__construct(UserContext::newContext($engineContext->getUser()->getId(), $engineContext->getApp()->getId()));

        $this->engineContext = $engineContext;
        $this->points        = $points;
    }

    /**
     * @return \Fusio\Engine\ContextInterface
     */
    public function getEngineContext()
    {
        return $this->engineContext;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }
}
