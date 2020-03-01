<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Service\Plan;

use Fusio\Impl\Service\Plan\DateCalculator;
use Fusio\Impl\Table;
use PHPUnit\Framework\TestCase;

/**
 * DateCalculatorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class DateCalculatorTest extends TestCase
{
    /**
     * @dataProvider dateProvider
     */
    public function testCalculate($startDate, $interval, $expect)
    {
        $endDate = (new DateCalculator())->calculate(new \DateTime($startDate), $interval);

        $this->assertEquals($expect, $endDate->format('Y-m-d'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCalculateInvalidInterval()
    {
        (new DateCalculator())->calculate(new \DateTime(), 10);
    }

    public function dateProvider()
    {
        return [
            ['2019-01-01', Table\Plan::INTERVAL_NONE, '2019-01-01'],
            ['2019-01-01', Table\Plan::INTERVAL_1MONTH, '2019-02-01'],
            ['2019-01-01', Table\Plan::INTERVAL_3MONTH, '2019-04-01'],
            ['2019-01-01', Table\Plan::INTERVAL_6MONTH, '2019-07-01'],
            ['2019-01-01', Table\Plan::INTERVAL_12MONTH, '2020-01-01'],

            ['2019-01-30', Table\Plan::INTERVAL_1MONTH, '2019-02-28'],
            ['2019-01-31', Table\Plan::INTERVAL_3MONTH, '2019-04-30'],
        ];
    }
}
