<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Consumer\Api\Page;

use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/page/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "id": 1,
    "title": "Overview",
    "slug": "overview",
    "content": "\n<div class=\"fusio-intro\">\n  <h1>Employ the Acme API<br> to power your app.<\/h1>\n  <p>Explore the documentation or dive<br>directly into the API reference.<\/p>\n  <p>\n    <a class=\"btn btn-primary btn-lg\" href=\".\/bootstrap\" role=\"button\">Get started<\/a>\n    <a class=\"btn btn-secondary btn-lg\" href=\".\/api\" role=\"button\">REST API<\/a>\n  <\/p>\n<\/div>\n\n<div class=\"fusio-intro-subline\">\n  <h2>Join the developer community.<\/h2>\n  <p>You can <a href=\".\/register\">register<\/a> a new account or <a href=\".\/login\">login<\/a>.<\/p>\n<\/div>\n\n<div class=\"row\">\n  <div class=\"col-md-4\">\n    <div class=\"fusio-intro-column\">\n      <div class=\"fusio-intro-column-icon text-primary\">\n        <i class=\"bi bi-cloudy-fill\"><\/i>\n      <\/div>\n      <h3><a href=\".\/bootstrap\" class=\"link-primary\">Documentation<\/a><\/h3>\n      <p>Explore guides which<br>help you get started quickly.<\/p>\n    <\/div>\n  <\/div>\n  <div class=\"col-md-4\">\n    <div class=\"fusio-intro-column\">\n      <div class=\"fusio-intro-column-icon text-primary\">\n        <i class=\"bi bi-box-fill\"><\/i>\n      <\/div>\n      <h3><a href=\".\/api\" class=\"link-primary\">API<\/a><\/h3>\n      <p>Dive directly into the<br>complete API reference.<\/p>\n    <\/div>\n  <\/div>\n  <div class=\"col-md-4\">\n    <div class=\"fusio-intro-column\">\n      <div class=\"fusio-intro-column-icon text-primary\">\n        <i class=\"bi bi-chat-fill\"><\/i>\n      <\/div>\n      <h3><a href=\".\/support\" class=\"link-primary\">Support<\/a><\/h3>\n      <p>Find all available<br>support options if you get stuck.<\/p>\n    <\/div>\n  <\/div>\n<\/div>",
    "date": "[datetime]"
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
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "id": 1,
    "title": "Overview",
    "slug": "overview",
    "content": "\n<div class=\"fusio-intro\">\n  <h1>Employ the Acme API<br> to power your app.<\/h1>\n  <p>Explore the documentation or dive<br>directly into the API reference.<\/p>\n  <p>\n    <a class=\"btn btn-primary btn-lg\" href=\".\/bootstrap\" role=\"button\">Get started<\/a>\n    <a class=\"btn btn-secondary btn-lg\" href=\".\/api\" role=\"button\">REST API<\/a>\n  <\/p>\n<\/div>\n\n<div class=\"fusio-intro-subline\">\n  <h2>Join the developer community.<\/h2>\n  <p>You can <a href=\".\/register\">register<\/a> a new account or <a href=\".\/login\">login<\/a>.<\/p>\n<\/div>\n\n<div class=\"row\">\n  <div class=\"col-md-4\">\n    <div class=\"fusio-intro-column\">\n      <div class=\"fusio-intro-column-icon text-primary\">\n        <i class=\"bi bi-cloudy-fill\"><\/i>\n      <\/div>\n      <h3><a href=\".\/bootstrap\" class=\"link-primary\">Documentation<\/a><\/h3>\n      <p>Explore guides which<br>help you get started quickly.<\/p>\n    <\/div>\n  <\/div>\n  <div class=\"col-md-4\">\n    <div class=\"fusio-intro-column\">\n      <div class=\"fusio-intro-column-icon text-primary\">\n        <i class=\"bi bi-box-fill\"><\/i>\n      <\/div>\n      <h3><a href=\".\/api\" class=\"link-primary\">API<\/a><\/h3>\n      <p>Dive directly into the<br>complete API reference.<\/p>\n    <\/div>\n  <\/div>\n  <div class=\"col-md-4\">\n    <div class=\"fusio-intro-column\">\n      <div class=\"fusio-intro-column-icon text-primary\">\n        <i class=\"bi bi-chat-fill\"><\/i>\n      <\/div>\n      <h3><a href=\".\/support\" class=\"link-primary\">Support<\/a><\/h3>\n      <p>Find all available<br>support options if you get stuck.<\/p>\n    <\/div>\n  <\/div>\n<\/div>",
    "date": "[datetime]"
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/page/1', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
