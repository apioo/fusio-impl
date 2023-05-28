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

namespace Fusio\Impl\Tests\Backend\Api\Schema;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * PreviewTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class PreviewTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/schema/preview/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/schema/preview/2', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/psx_model_Object([0-9A-Fa-f]{8})/', '[dynamic_id]', $body);

        $expect = <<<'JSON'
{
    "preview": "<div id=\"Message\" class=\"psx-object psx-struct\"><h1><a class=\"psx-type-link\" data-name=\"Message\">Message<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"success\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Boolean<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"message\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"id\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">success<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"Boolean\">Boolean<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">message<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">id<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/schema/preview/2', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/schema/preview/2', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
