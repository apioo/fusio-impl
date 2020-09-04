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

namespace Fusio\Impl\Framework\Loader;

use Fusio\Engine\Model\AppInterface;
use Fusio\Engine\Model\TokenInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Impl\Authorization\UserContext;
use PSX\Framework\Loader\Context as FrameworkContext;

/**
 * Context
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Context extends FrameworkContext
{
    /**
     * @var integer
     */
    protected $routeId;

    /**
     * @var \Fusio\Engine\Model\AppInterface
     */
    protected $app;

    /**
     * @var \Fusio\Engine\Model\UserInterface
     */
    protected $user;

    /**
     * @var \Fusio\Engine\Model\TokenInterface
     */
    protected $token;

    /**
     * @var integer
     */
    protected $logId;

    /**
     * @var array
     */
    protected $method;

    /**
     * @var \Fusio\Impl\Authorization\UserContext
     */
    private $userContext;

    /**
     * @return integer
     */
    public function getRouteId()
    {
        return $this->routeId;
    }

    /**
     * @param integer $routeId
     */
    public function setRouteId($routeId)
    {
        $this->routeId = $routeId;
    }

    /**
     * @return \Fusio\Engine\Model\AppInterface
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return integer
     */
    public function getAppId()
    {
        return $this->app->getId();
    }

    /**
     * @param \Fusio\Engine\Model\AppInterface $app
     */
    public function setApp(AppInterface $app)
    {
        $this->app = $app;
    }

    /**
     * @return \Fusio\Engine\Model\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->user->getId();
    }

    /**
     * @param \Fusio\Engine\Model\UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return \Fusio\Engine\Model\TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param \Fusio\Engine\Model\TokenInterface $token
     */
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * @return integer
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * @param integer $logId
     */
    public function setLogId($logId)
    {
        $this->logId = $logId;
    }

    /**
     * @return array
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param array $method
     */
    public function setMethod(array $method)
    {
        $this->method = $method;
    }

    /**
     * @return \Fusio\Impl\Authorization\UserContext
     */
    public function getUserContext()
    {
        if ($this->userContext) {
            return $this->userContext;
        }

        return $this->userContext = UserContext::newContext($this->user->getId(), $this->app->getId());
    }
}
