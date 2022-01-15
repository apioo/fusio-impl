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

namespace Fusio\Impl\Tests\Service\User;

use Fusio\Impl\Service\User\Validator;
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
     * @dataProvider assertProvider
     */
    public function testAssertPassword($password, $minLength, $minAlpha, $minNumeric, $minSpecial)
    {
        Validator::assertPassword($password, $minLength, $minAlpha, $minNumeric, $minSpecial);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider assertProviderFail
     */
    public function testAssertPasswordFail($password, $minLength, $minAlpha, $minNumeric, $minSpecial, $exceptionMessage)
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage($exceptionMessage);

        Validator::assertPassword($password, $minLength, $minAlpha, $minNumeric, $minSpecial);
    }

    public function assertProvider()
    {
        return [
            ['aaaaaaaa', null, null, null, null, true],
            ['aaaaaaa1', null, null, null, null, true],
            ['00000000', null, null, null, null, true],
            ['aaaaaa!1', null, null, null, null, true],
            ['aaaaaaaa', null, 0, 0, 0, true],
            ['aaaaaaa1', null, 0, 0, 0, true],
            ['00000000', null, 0, 0, 0, true],
            ['aaaa#_11', null, 2, 2, 2, true],
        ];
    }

    public function assertProviderFail()
    {
        return [
            ['', null, null, null, null, 'Password must not be empty'],
            ['a', null, null, null, null, 'Password must have at least 8 characters'],
            ["\0" . 'aaaaa!1', null, null, null, null, 'Password must contain only printable ascii characters (0x21-0x7E)'],
            ['aaaaaa', 4, null, null, null, 'Password must have at least 8 characters'], // if length < 8 we use 8
            ['aaaaaa', 12, null, null, null, 'Password must have at least 12 characters'],
            ['aaaaaaaa', null, 2, 2, 2, 'Password must have at least 2 numeric character (0-9)'],
            ['aaaaaa11', null, 2, 2, 2, 'Password must have at least 2 special character i.e. (!#$%&*@_~)'],
            ['00000000', null, 2, 2, 2, 'Password must have at least 2 alphabetic character (a-z, A-Z)'],
        ];
    }
}
