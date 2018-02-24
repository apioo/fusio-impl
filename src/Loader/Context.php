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

namespace Fusio\Impl\Loader;

use Fusio\Impl\Authorization\UserContext;

/**
 * Context
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Context extends \PSX\Framework\Loader\Context
{
    /**
     * @var integer
     */
    protected $routeId;

    /**
     * @var integer
     */
    protected $appId;

    /**
     * @var integer
     */
    protected $userId;

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
     * @return integer
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param integer $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
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
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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

        return $this->userContext = UserContext::newContext($this->userId, $this->appId);
    }
}
