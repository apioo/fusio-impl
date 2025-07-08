<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * MostUsedAppsTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MostUsedAppsTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/statistic/most_used_apps?from=2015-06-01T00:00:00&to=2015-06-30T23:59:59', 'GET', array(
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
    "series": [
        {
            "name": "Foo-App",
            "data": [
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
                2,
                0,
                0,
                0,
                0,
                0
            ]
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
