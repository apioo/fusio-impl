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

namespace Fusio\Impl\Tests\Service\Route;

use Fusio\Impl\Backend\Filter\Route\Path;
use Fusio\Impl\Service\Route\Validator;
use PHPUnit\Framework\TestCase;
use PSX\Http\Exception\BadRequestException;

/**
 * ValidatorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ValidatorTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testAssertPath(string $path, bool $expect, ?string $errorMessage)
    {
        try {
            Validator::assertPath($path);

            $this->assertTrue($expect);
        } catch (BadRequestException $e) {
            $this->assertFalse($expect);
            $this->assertEquals($errorMessage, $e->getMessage());
        }
    }

    public function pathProvider(): array
    {
        return [
            ['', false, 'Path must not be empty'],
            ['foo', false, 'Path must start with a /'],
            ['/', true, null],
            ['//', false, 'Path has an empty path segment'],
            ['/foo', true, null],
            ['/foo/:bar', true, null],
            ['/foo/$bar<[0-9]+>', true, null],
            ['/foo/!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~', true, null],
            ['/foo' . "\x20\x7F", false, 'Path contains invalid characters inside a path segment'],
            ['/foo' . "\x80", false, 'Path contains invalid characters inside a path segment'],
            ['/backend', false, 'Path uses a path segment which is reserved for the system'],
            ['/consumer', false, 'Path uses a path segment which is reserved for the system'],
            ['/system', false, 'Path uses a path segment which is reserved for the system'],
            ['/authorization', false, 'Path uses a path segment which is reserved for the system'],
        ];
    }
}
