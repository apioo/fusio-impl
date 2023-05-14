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

namespace Fusio\Impl\Tests\Backend\Api\Statistic;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ErrorsPerRouteTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ErrorsPerRouteTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/statistic/errors_per_route?from=2015-06-01T00:00:00&to=2015-06-30T23:59:59', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $expect = <<<JSON
{
    "labels": [
        "2015-06-01",
        "2015-06-02",
        "2015-06-03",
        "2015-06-04",
        "2015-06-05",
        "2015-06-06",
        "2015-06-07",
        "2015-06-08",
        "2015-06-09",
        "2015-06-10",
        "2015-06-11",
        "2015-06-12",
        "2015-06-13",
        "2015-06-14",
        "2015-06-15",
        "2015-06-16",
        "2015-06-17",
        "2015-06-18",
        "2015-06-19",
        "2015-06-20",
        "2015-06-21",
        "2015-06-22",
        "2015-06-23",
        "2015-06-24",
        "2015-06-25",
        "2015-06-26",
        "2015-06-27",
        "2015-06-28",
        "2015-06-29",
        "2015-06-30"
    ],
    "data": [
        [
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            1,
            0,
            0,
            0,
            0,
            0
        ]
    ],
    "series": [
        "\/foo"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
