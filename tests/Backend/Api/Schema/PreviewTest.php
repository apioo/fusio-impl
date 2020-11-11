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
    "preview": "<div id=\"User\" class=\"psx-object psx-struct\"><h1><a href=\"#User\">User<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"id\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"status\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"name\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"email\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"points\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"scopes\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Array (String)<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"apps\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Array (Object (<a href=\"#App\">App<\/a>))<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"attributes\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Object (<a href=\"#User_Attributes\">User_Attributes<\/a>)<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"date\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">id<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">status<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">name<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><dl class=\"psx-property-constraint\"><dt>Pattern<\/dt><dd><span class=\"psx-constraint-pattern\">^[a-zA-Z0-9\\-\\_\\.]{3,32}$<\/span><\/dd><\/dl><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">email<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">points<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">scopes<\/span><\/td><td><span class=\"psx-property-type\">Array (String)<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">apps<\/span><\/td><td><span class=\"psx-property-type\">Array (Object (<a href=\"#App\">App<\/a>))<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">attributes<\/span><\/td><td><span class=\"psx-property-type\">Object (<a href=\"#User_Attributes\">User_Attributes<\/a>)<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">date<\/span><\/td><td><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n\n<div id=\"App\" class=\"psx-object psx-struct\"><h1><a href=\"#App\">App<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"id\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"userId\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"status\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"name\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"url\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"parameters\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"appKey\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"appSecret\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"date\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"scopes\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Array (String)<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"tokens\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Array (Object (<a href=\"#App_Token\">App_Token<\/a>))<\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">id<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">userId<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">status<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">name<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><dl class=\"psx-property-constraint\"><dt>Pattern<\/dt><dd><span class=\"psx-constraint-pattern\">^[a-zA-Z0-9\\-\\_]{3,64}$<\/span><\/dd><\/dl><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">url<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">parameters<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">appKey<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">appSecret<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">date<\/span><\/td><td><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">scopes<\/span><\/td><td><span class=\"psx-property-type\">Array (String)<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">tokens<\/span><\/td><td><span class=\"psx-property-type\">Array (Object (<a href=\"#App_Token\">App_Token<\/a>))<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n\n<div id=\"App_Token\" class=\"psx-object psx-struct\"><h1><a href=\"#App_Token\">App_Token<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"id\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Integer<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"token\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"scope\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"ip\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"expire\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"date\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">id<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">token<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">scope<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">ip<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">expire<\/span><\/td><td><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">date<\/span><\/td><td><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\">DateTime<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n\n<div id=\"User_Attributes\" class=\"psx-object psx-map\"><h1><a href=\"#User_Attributes\">User_Attributes<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"*\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">*<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n"
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
