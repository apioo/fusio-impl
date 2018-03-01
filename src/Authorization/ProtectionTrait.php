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

namespace Fusio\Impl\Authorization;

use Fusio\Impl\Filter\Authentication;
use PSX\Http\Filter\CORS;
use PSX\Http\Filter\UserAgentEnforcer;

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
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    /**
     * @Inject
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @Inject
     * @var \Fusio\Engine\Repository\AppInterface
     */
    protected $appRepository;

    /**
     * @Inject
     * @var \Fusio\Engine\Repository\UserInterface
     */
    protected $userRepository;

    public function getPreFilter()
    {
        // it is required for every request to have an user agent which
        // identifies the client
        $filter[] = new UserAgentEnforcer();

        // cors header
        $allowOrigin = $this->config->get('fusio_cors');
        if (!empty($allowOrigin)) {
            $filter[] = new CORS($allowOrigin);
        }

        $filter[] = new Authentication(
            $this->connection,
            $this->context,
            $this->config->get('fusio_project_key'),
            $this->appRepository,
            $this->userRepository
        );

        return $filter;
    }
}
