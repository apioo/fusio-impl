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

namespace Fusio\Impl\Tests\Backend\Filter\Routes;

use Fusio\Impl\Backend\Filter\Routes\Path;

/**
 * PathTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testApply($path, $expect, $errorMessage)
    {
        $filter = new Path();

        $this->assertEquals($expect, $filter->apply($path));

        if ($expect === false) {
            $this->assertEquals($errorMessage, $filter->getErrorMessage());
        }
    }

    public function pathProvider()
    {
        return [
            ['', false, '%s is not a valid path'],
            ['foo', false, '%s must start with a /'],
            ['/', true, null],
            ['//', false, '%s has an empty path segment'],
            ['/foo', true, null],
            ['/foo/:bar', true, null],
            ['/foo/$bar<[0-9]+>', true, null],
            ['/foo/!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~', true, null],
            ['/foo' . "\x20\x7F", false, '%s contains invalid characters inside a path segment'],
            ['/foo' . "\x80", false, '%s contains invalid characters inside a path segment'],
            ['/backend', false, '%s uses a path segment which is reserved for the system'],
            ['/consumer', false, '%s uses a path segment which is reserved for the system'],
            ['/doc', false, '%s uses a path segment which is reserved for the system'],
            ['/authorization', false, '%s uses a path segment which is reserved for the system'],
            ['/export', false, '%s uses a path segment which is reserved for the system'],
        ];
    }
}
