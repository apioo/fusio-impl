<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Export;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

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

    public function testGetResource()
    {
        $response = $this->sendRequest('/export/openapi/*/foo', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resources/openapi_resource.json');

        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCollectionExternal()
    {
        $response = $this->sendRequest('/export/openapi/*/*', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resources/openapi_collection_external.json');

        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCollectionInternal()
    {
        $response = $this->sendRequest('/export/openapi/*/*?filter=internal', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resources/openapi_collection_internal.json');

        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
