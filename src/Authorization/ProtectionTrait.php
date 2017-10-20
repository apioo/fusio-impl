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

namespace Fusio\Impl\Authorization;

use PSX\Framework\Filter\CORS;
use PSX\Framework\Filter\UserAgentEnforcer;

/**
 * ProtectionTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
trait ProtectionTrait
{
    /**
     * @Inject
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * ID of the app
     *
     * @var integer
     */
    protected $appId;

    /**
     * ID of the authenticated user
     *
     * @var integer
     */
    protected $userId;

    /**
     * @var \Fusio\Impl\Authorization\UserContext
     */
    protected $userContext;

    public function getPreFilter()
    {
        $filter = array();

        $filter[] = new UserAgentEnforcer();

        $filter[] = new Oauth2Filter(
            $this->connection,
            $this->request->getMethod(),
            $this->context->get('fusio.routeId'),
            $this->config->get('fusio_project_key'),
            function ($accessToken) {
                $this->appId       = $accessToken['appId'];
                $this->userId      = $accessToken['userId'];
                $this->userContext = UserContext::newContext($accessToken['userId'], $accessToken['appId']);
            }
        );

        $allowOrigin = $this->config->get('fusio_cors');
        if (!empty($allowOrigin)) {
            $filter[] = new CORS($allowOrigin);
        }

        return $filter;
    }
}
