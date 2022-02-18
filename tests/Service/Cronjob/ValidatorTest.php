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

namespace Fusio\Impl\Tests\Service\Cronjob;

use Fusio\Impl\Backend\Filter\Cronjob\Cron;
use Fusio\Impl\Service\Cronjob\Validator;
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
    public function testAssertCron(string $cron, bool $expect, ?string $errorMessage)
    {
        try {
            Validator::assertCron($cron);

            $this->assertTrue($expect);
        } catch (BadRequestException $e) {
            $this->assertFalse($expect);
            $this->assertEquals($errorMessage, $e->getMessage());
        }
    }

    public function pathProvider()
    {
        return [
            ['', false, 'Cron must not be empty'],
            ['foo', false, 'Cron must have exactly 5 space separated fields'],
            ['59 0 a * *', false, 'Cron day is not valid'],
            ['60 0 * * *', false, 'Cron minute must be lower or equal 59'],
            ['5 24 * * *', false, 'Cron hour must be lower or equal 23'],
            ['5 0 32 * *', false, 'Cron day must be lower or equal 31'],
            ['5 0 0 * *', false, 'Cron day must be greater or equal 1'],
            ['5 0 * 13 *', false, 'Cron month must be lower or equal 12'],
            ['5 0 * 0 *', false, 'Cron month must be greater or equal 1'],
            ['5 0 * * 8', false, 'Cron weekday must be lower or equal 7'],

            ['5 0 * * *', true, null],
            ['*/5 * * * *', true, null],
            ['15 14 1 * *', true, null],
            ['0 22 * * 1-5', true, null],
            ['23 0-23/2 * * *', true, null],
            ['5 4 * * sun', true, null], // we convert names
            ['0 4 8-14 * *', true, null],
            ['@daily', true, null],

            ['5  0  *    *  *', true, null], // fix spaces
            ['05 00 * * *', true, null], // convert to int
        ];
    }
}
