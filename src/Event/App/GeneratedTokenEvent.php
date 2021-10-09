<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Event\App;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;

/**
 * GeneratedTokenEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GeneratedTokenEvent extends EventAbstract
{
    /**
     * @var integer
     */
    protected $appId;

    /**
     * @var integer
     */
    protected $tokenId;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var array
     */
    protected $scopes;

    /**
     * @var \DateTime
     */
    protected $expires;

    /**
     * @var \DateTime
     */
    protected $now;

    /**
     * @param integer $appId
     * @param integer $tokenId
     * @param string $accessToken
     * @param array $scopes
     * @param \DateTime $expires
     * @param \DateTime $now
     * @param \Fusio\Impl\Authorization\UserContext $context
     */
    public function __construct($appId, $tokenId, $accessToken, array $scopes, \DateTime $expires, \DateTime $now, UserContext $context)
    {
        parent::__construct($context);

        $this->appId       = $appId;
        $this->tokenId     = $tokenId;
        $this->accessToken = $accessToken;
        $this->scopes      = $scopes;
        $this->expires     = $expires;
        $this->now         = $now;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getTokenId()
    {
        return $this->tokenId;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function getNow()
    {
        return $this->now;
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}
