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
            ['foo', false, 'foo is not a valid CRON expression'],
            ['59 0 a * *', false, 'Invalid CRON field value a at position 2'],
            ['60 0 * * *', false, 'Invalid CRON field value 60 at position 0'],
            ['5 24 * * *', false, 'Invalid CRON field value 24 at position 1'],
            ['5 0 32 * *', false, 'Invalid CRON field value 32 at position 2'],
            ['5 0 0 * *', false, 'Invalid CRON field value 0 at position 2'],
            ['5 0 * 13 *', false, 'Invalid CRON field value 13 at position 3'],
            ['5 0 * 0 *', false, 'Invalid CRON field value 0 at position 3'],
            ['5 0 * * 8', false, 'Invalid CRON field value 8 at position 4'],

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
