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

namespace Fusio\Impl\Tests\Backend\Api\Statistic;

use Fusio\Impl\Tests\DbTestCase;

/**
 * UserRegistrationsTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserRegistrationsTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/statistic/user_registrations?from=2018-10-01T00:00:00&to=2018-10-31T23:59:59', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $expect = <<<JSON
{
    "labels": [
        "2018-10-01",
        "2018-10-02",
        "2018-10-03",
        "2018-10-04",
        "2018-10-05",
        "2018-10-06",
        "2018-10-07",
        "2018-10-08",
        "2018-10-09",
        "2018-10-10",
        "2018-10-11",
        "2018-10-12",
        "2018-10-13",
        "2018-10-14",
        "2018-10-15",
        "2018-10-16",
        "2018-10-17",
        "2018-10-18",
        "2018-10-19",
        "2018-10-20",
        "2018-10-21",
        "2018-10-22",
        "2018-10-23",
        "2018-10-24",
        "2018-10-25",
        "2018-10-26",
        "2018-10-27",
        "2018-10-28",
        "2018-10-29",
        "2018-10-30",
        "2018-10-31"
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
            0,
            0,
            0,
            0,
            0,
            0,
            0
        ]
    ],
    "series": [
        "Users"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
