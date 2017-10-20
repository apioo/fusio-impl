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

namespace Fusio\Impl\Backend\Authorization;

use PSX\Framework\Filter\CORS;
use PSX\Framework\Oauth2\TokenAbstract;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Token extends TokenAbstract
{
    /**
     * @Inject("backend_grant_type_factory")
     * @var \PSX\Framework\Oauth2\GrantTypeFactory
     */
    protected $grantTypeFactory;

    public function getPreFilter()
    {
        $filter = parent::getPreFilter();

        // cors header
        $allowOrigin = $this->config->get('fusio_cors');
        if (!empty($allowOrigin)) {
            $filter[] = new CORS($allowOrigin);
        }

        return $filter;
    }
}
