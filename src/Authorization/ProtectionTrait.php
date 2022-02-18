<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Doctrine\DBAL\Connection;
use Fusio\Impl\Controller\Filter\Authentication;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Security\TokenValidator;
use PSX\Dependency\Attribute\Inject;
use PSX\Http\Filter\UserAgentEnforcer;

/**
 * ProtectionTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
trait ProtectionTrait
{
    protected Context $context;

    #[Inject]
    protected Connection $connection;

    #[Inject]
    protected TokenValidator $securityTokenValidator;

    public function getPreFilter()
    {
        // it is required for every request to have an user agent which identifies the client
        $filter[] = new UserAgentEnforcer();

        $filter[] = new Authentication(
            $this->securityTokenValidator,
            $this->context
        );

        return $filter;
    }
}
