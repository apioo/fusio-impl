<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Event\Routes;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * UpdatedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class UpdatedEvent extends EventAbstract
{
    protected $routeId;
    protected $record;
    protected $config;
    protected $route;

    public function __construct($routeId, array $record, $config, $route, UserContext $context)
    {
        parent::__construct($context);

        $this->routeId = $routeId;
        $this->record  = $record;
        $this->config  = $config;
        $this->route   = $route;
    }

    public function getRouteId()
    {
        return $this->routeId;
    }

    public function getRecord()
    {
        return $this->record;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getRoute()
    {
        return $this->route;
    }
}
