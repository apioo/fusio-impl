<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/schema/preview/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/schema/preview/2', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/psx_model_Object([0-9A-Fa-f]{8})/', '[dynamic_id]', $body);

        $expect = <<<'JSON'
{
    "preview": "<div id=\"psx_model_Test\" class=\"psx-object\"><h1>test<\/h1><small>http:\/\/phpsx.org<\/small><table class=\"table psx-type-properties\"><colgroup><col width=\"20%\" \/><col width=\"20%\" \/><col width=\"40%\" \/><col width=\"20%\" \/><\/colgroup><thead><tr><th>Property<\/th><th>Type<\/th><th>Description<\/th><th>Constraints<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">totalResults<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">itemsPerPage<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">startIndex<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">entry<\/span><\/td><td><span class=\"psx-property-type psx-property-type-array\">Array (<span class=\"psx-property-type psx-property-type-complex\"><a href=\"#[dynamic_id]\">Object<\/a><\/span>)<\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><\/tbody><\/table><\/div><div id=\"[dynamic_id]\" class=\"psx-object\"><h1>Object<\/h1><table class=\"table psx-type-properties\"><colgroup><col width=\"20%\" \/><col width=\"20%\" \/><col width=\"40%\" \/><col width=\"20%\" \/><\/colgroup><thead><tr><th>Property<\/th><th>Type<\/th><th>Description<\/th><th>Constraints<\/th><\/tr><\/thead><tbody><tr><td><span class=\"psx-property-name psx-property-optional\">id<\/span><\/td><td><span class=\"psx-property-type\">Integer<\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">title<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">content<\/span><\/td><td><span class=\"psx-property-type\">String<\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><tr><td><span class=\"psx-property-name psx-property-optional\">date<\/span><\/td><td><span class=\"psx-property-type\"><a href=\"http:\/\/tools.ietf.org\/html\/rfc3339#section-5.6\" title=\"RFC3339\">DateTime<\/a><\/span><\/td><td><span class=\"psx-property-description\"><\/span><\/td><td><\/td><\/tr><\/tbody><\/table><\/div>"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/schema/preview/2', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/schema/preview/2', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

}
