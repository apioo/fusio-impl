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

namespace Fusio\Impl\Tests\Backend\View;

use Fusio\Engine\Request\HttpRequest;
use PHPUnit\Framework\TestCase;
use PSX\Http\Environment\HttpContext;
use PSX\Http\Request;
use PSX\Record\Record;
use PSX\Uri\Uri;

/**
 * FilterTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class FilterTestCase extends TestCase
{
    protected function createRequest(array $parameters): HttpRequest
    {
        $uri = new Uri('/');
        $uri = $uri->withParameters($parameters);

        $context = new HttpContext(new Request($uri, 'GET'), []);

        return new HttpRequest($context, new Record());
    }
}
