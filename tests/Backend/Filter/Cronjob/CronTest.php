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

namespace Fusio\Impl\Tests\Backend\Filter\Cronjob;

use Fusio\Impl\Backend\Filter\Cronjob\Cron;
use PHPUnit\Framework\TestCase;

/**
 * CronTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CronTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testApply($cron, $expect, $errorMessage)
    {
        $filter = new Cron();

        $this->assertEquals($expect, $filter->apply($cron));

        if ($expect === false) {
            $this->assertEquals($errorMessage, $filter->getErrorMessage());
        }
    }

    public function pathProvider()
    {
        return [
            ['', false, '%s is not a valid cron expression'],
            ['foo', false, '%s must have exactly 5 space separated fields'],
            ['59 0 a * *', false, '%s day is not valid'],
            ['60 0 * * *', false, '%s minute must be lower or equal 59'],
            ['5 24 * * *', false, '%s hour must be lower or equal 23'],
            ['5 0 32 * *', false, '%s day must be lower or equal 31'],
            ['5 0 0 * *', false, '%s day must be greater or equal 1'],
            ['5 0 * 13 *', false, '%s month must be lower or equal 12'],
            ['5 0 * 0 *', false, '%s month must be greater or equal 1'],
            ['5 0 * * 8', false, '%s weekday must be lower or equal 7'],

            ['5 0 * * *', '5 0 * * *', null],
            ['*/5 * * * *', '*/5 * * * *', null],
            ['15 14 1 * *', '15 14 1 * *', null],
            ['0 22 * * 1-5', '0 22 * * 1-5', null],
            ['23 0-23/2 * * *', '23 0-23/2 * * *', null],
            ['5 4 * * sun', '5 4 * * 7', null], // we convert names
            ['0 4 8-14 * *', '0 4 8-14 * *', null],
            ['@daily', '@daily', null],

            ['5  0  *    *  *', '5 0 * * *', null], // fix spaces
            ['05 00 * * *', '5 0 * * *', null], // convert to int
        ];
    }
}
