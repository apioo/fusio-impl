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
    "preview": "<div id=\"About\" class=\"psx-object psx-struct\"><h1><a class=\"psx-type-link\" data-name=\"About\">About<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"apiVersion\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"title\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"description\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"termsOfService\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"contactName\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"contactUrl\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"contactEmail\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"licenseName\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"licenseUrl\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"paymentCurrency\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"categories\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Array (String)<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"scopes\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Array (String)<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"apps\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">AboutApps<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"links\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">Array (AboutLink)<\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">apiVersion<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">title<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">description<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">termsOfService<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">contactName<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">contactUrl<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">contactEmail<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">licenseName<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">licenseUrl<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">paymentCurrency<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">categories<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"Array (String)\">Array (String)<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">scopes<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"Array (String)\">Array (String)<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">apps<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"AboutApps\">AboutApps<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">links<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"Array (AboutLink)\">Array (AboutLink)<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n\n<div id=\"AboutApps\" class=\"psx-object psx-map\"><h1><a class=\"psx-type-link\" data-name=\"AboutApps\">AboutApps<\/a><\/h1><pre class=\"psx-object-json\">Map (String)<\/pre><\/div>\n\n<div id=\"AboutLink\" class=\"psx-object psx-struct\"><h1><a class=\"psx-type-link\" data-name=\"AboutLink\">AboutLink<\/a><\/h1><pre class=\"psx-object-json\"><span class=\"psx-object-json-pun\">{<\/span>\n  <span class=\"psx-object-json-key\">\"rel\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n  <span class=\"psx-object-json-key\">\"href\"<\/span><span class=\"psx-object-json-pun\">: <\/span><span class=\"psx-property-type\">String<\/span><span class=\"psx-object-json-pun\">,<\/span>\n<span class=\"psx-object-json-pun\">}<\/span><\/pre><table class=\"table psx-object-properties\"><colgroup><col width=\"30%\" \/><col width=\"70%\" \/><\/colgroup><thead><tr><th>Field<\/th><th>Description<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">rel<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">href<\/span><\/td><td><span class=\"psx-property-type\"><a class=\"psx-type-link\" data-name=\"String\">String<\/a><\/span><br \/><div class=\"psx-property-description\"><\/div><\/td><\/tr><\/tbody><\/table><\/div>\n"
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
