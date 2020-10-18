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

namespace Fusio\Impl\Tests\System;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * OpenAPITest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class OpenAPITest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testGetResource($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/export/openapi/*/foo', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resources/openapi_resource.json');

        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testGetCollectionExternal($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/export/openapi/*/*', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resources/openapi_collection_external.json');

        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testGetCollectionInternal($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/export/openapi/*/*?filter=internal', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = __DIR__ . '/resources/openapi_collection_internal.json';
        $actual = __DIR__ . '/resources/openapi_collection_internal_actual.json';

        file_put_contents($actual, $body);

        $this->assertJsonFileEqualsJsonFile($expect, $actual);
    }

    public function providerDebugStatus()
    {
        return [
            [true],
            [false],
        ];
    }
}
