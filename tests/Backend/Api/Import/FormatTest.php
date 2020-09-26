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

namespace Fusio\Impl\Tests\Backend\Api\Import;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * FormatTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class FormatTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/import/:format', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/format.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    /**
     * @dataProvider providerSpecs
     */
    public function testPost($case)
    {
        $spec   = file_get_contents(__DIR__ . '/resource/' . $case . '.json');
        $expect = file_get_contents(__DIR__ . '/resource/' . $case . '_expect.json');

        $body = json_encode(['schema' => $spec]);

        $response = $this->sendRequest('/backend/import/openapi', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf',
            'Content-Type'  => 'application/json',
        ), $body);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function providerSpecs()
    {
        return [
            ['openapi_case01'],
            ['openapi_case03'],
        ];
    }
}
