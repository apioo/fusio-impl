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

namespace Fusio\Impl\Tests\Backend\Api\Schema;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * PreviewTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PreviewTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/schema/preview/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/preview.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/schema/preview/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
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
    "preview": "<div id=\"Entry\" class=\"psx-object psx-struct\"><h1><a href=\"#Entry\">Entry<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"id\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"title\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"content\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"date\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">id<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">title<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">content<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">date<\/span><\/td><td><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n\n<div id=\"Collection\" class=\"psx-object psx-struct\"><h1><a href=\"#Collection\">Collection<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"totalResults\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"itemsPerPage\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"startIndex\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"entry\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Object (<a href=\"#Entry\">Entry<\/a>)<\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">totalResults<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">itemsPerPage<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">startIndex<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">entry<\/span><\/td><td><span class=\"psx-property-type\">Object (<a href=\"#Entry\">Entry<\/a>)<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n"
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

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/schema/preview/2', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
