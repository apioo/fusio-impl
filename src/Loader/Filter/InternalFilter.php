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

namespace Fusio\Impl\Loader\Filter;

use Fusio\Impl\Backend\Filter\Routes\Path;
use PSX\Api\Listing\FilterInterface;

/**
 * InternalFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class InternalFilter implements FilterInterface
{
    protected $paths;

    public function __construct()
    {
        $this->paths = Path::getReserved();
    }

    public function match($path)
    {
        foreach ($this->paths as $part) {
            if (substr($path, 1, strlen($part)) == $part) {
                return true;
            }
        }
        return false;
    }

    public function getId()
    {
        return 'internal';
    }
}
