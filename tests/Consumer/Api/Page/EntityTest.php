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

namespace Fusio\Impl\Tests\Consumer\Api\Page;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/consumer/page/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/entity.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/page/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "id": 1,
    "title": "Overview",
    "slug": "overview",
    "content": "\n<div class=\"fusio-intro\">\n<h1>Employ the Acme API<br> to power your app.<\/h1>\n<p>Explore the documentation or dive<br>directly into the API reference.<\/p>\n<p>\n<a class=\"btn btn-primary btn-lg\" href=\"\/bootstrap\" role=\"button\">Get started<\/a>\n<a class=\"btn btn-secondary btn-lg\" href=\"\/api\" role=\"button\">REST API<\/a>\n<\/p>\n<\/div>\n\n<div class=\"fusio-intro-subline\">\n<h2>Join the developer community.<\/h2>\n<p>You can <a href=\"\/register\">register<\/a> a new account or <a href=\"\/login\">login<\/a>.<\/p>\n<\/div>\n\n<div class=\"row\">\n<div class=\"col-md-4\">\n<div class=\"fusio-intro-column\">\n<div class=\"fusio-intro-column-icon text-primary\">\n<i class=\"bi bi-cloudy-fill\"><\/i>\n<\/div>\n<h3><a href=\"\/bootstrap\" class=\"link-primary\">Documentation<\/a><\/h3>\n<p>Explore guides which<br>help you get started quickly.<\/p>\n<\/div>\n<\/div>\n<div class=\"col-md-4\">\n<div class=\"fusio-intro-column\">\n<div class=\"fusio-intro-column-icon text-primary\">\n<i class=\"bi bi-box-fill\"><\/i>\n<\/div>\n<h3><a href=\"\/api\" class=\"link-primary\">API<\/a><\/h3>\n<p>Dive directly into the<br>complete API reference.<\/p>\n<\/div>\n<\/div>\n<div class=\"col-md-4\">\n<div class=\"fusio-intro-column\">\n<div class=\"fusio-intro-column-icon text-primary\">\n<i class=\"bi bi-chat-fill\"><\/i>\n<\/div>\n<h3><a href=\"\/support\" class=\"link-primary\">Support<\/a><\/h3>\n<p>Find all available<br>support options if you get stuck.<\/p>\n<\/div>\n<\/div>\n<\/div>",
    "date": "2021-07-03T13:53:09Z"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/consumer/page/~overview', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "id": 1,
    "title": "Overview",
    "slug": "overview",
    "content": "\n<div class=\"fusio-intro\">\n<h1>Employ the Acme API<br> to power your app.<\/h1>\n<p>Explore the documentation or dive<br>directly into the API reference.<\/p>\n<p>\n<a class=\"btn btn-primary btn-lg\" href=\"\/bootstrap\" role=\"button\">Get started<\/a>\n<a class=\"btn btn-secondary btn-lg\" href=\"\/api\" role=\"button\">REST API<\/a>\n<\/p>\n<\/div>\n\n<div class=\"fusio-intro-subline\">\n<h2>Join the developer community.<\/h2>\n<p>You can <a href=\"\/register\">register<\/a> a new account or <a href=\"\/login\">login<\/a>.<\/p>\n<\/div>\n\n<div class=\"row\">\n<div class=\"col-md-4\">\n<div class=\"fusio-intro-column\">\n<div class=\"fusio-intro-column-icon text-primary\">\n<i class=\"bi bi-cloudy-fill\"><\/i>\n<\/div>\n<h3><a href=\"\/bootstrap\" class=\"link-primary\">Documentation<\/a><\/h3>\n<p>Explore guides which<br>help you get started quickly.<\/p>\n<\/div>\n<\/div>\n<div class=\"col-md-4\">\n<div class=\"fusio-intro-column\">\n<div class=\"fusio-intro-column-icon text-primary\">\n<i class=\"bi bi-box-fill\"><\/i>\n<\/div>\n<h3><a href=\"\/api\" class=\"link-primary\">API<\/a><\/h3>\n<p>Dive directly into the<br>complete API reference.<\/p>\n<\/div>\n<\/div>\n<div class=\"col-md-4\">\n<div class=\"fusio-intro-column\">\n<div class=\"fusio-intro-column-icon text-primary\">\n<i class=\"bi bi-chat-fill\"><\/i>\n<\/div>\n<h3><a href=\"\/support\" class=\"link-primary\">Support<\/a><\/h3>\n<p>Find all available<br>support options if you get stuck.<\/p>\n<\/div>\n<\/div>\n<\/div>",
    "date": "2021-07-03T13:53:09Z"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/page/1', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/page/1', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/page/1', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
