<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Service\User;

use Fusio\Impl\Service\User\Validator;
use PHPUnit\Framework\TestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Exception\BadRequestException;

/**
 * ValidatorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ValidatorTest extends TestCase
{
    /**
     * @dataProvider assertProvider
     */
    public function testAssertPassword($password, $minLength, $minAlpha, $minNumeric, $minSpecial)
    {
        $validator = Environment::getService(Validator::class);
        $validator->assertPassword($password, $minLength, $minAlpha, $minNumeric, $minSpecial);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider assertProviderFail
     */
    public function testAssertPasswordFail($password, $minLength, $minAlpha, $minNumeric, $minSpecial, $exceptionMessage)
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $validator = Environment::getService(Validator::class);
        $validator->assertPassword($password, $minLength, $minAlpha, $minNumeric, $minSpecial);
    }

    public function assertProvider(): array
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
