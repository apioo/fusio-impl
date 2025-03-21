<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;
use PSX\Data\Exception\UploadException;

/**
 * MimeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MimeTest extends DbTestCase
{
    public function testBinary()
    {
        $response = $this->sendRequest('/mime/binary', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873',
            'Content-Type' => 'application/octet-stream'
        ], 'foobar');

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "class": "PSX.Http.Stream.StringStream",
    "raw": "foobar"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testForm()
    {
        $response = $this->sendRequest('/mime/form', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873',
            'Content-Type' => 'application/x-www-form-urlencoded'
        ], 'foo=bar');

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "class": "PSX.Data.Body.Form",
    "raw": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testJson()
    {
        $response = $this->sendRequest('/mime/json', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873',
            'Content-Type' => 'application/json'
        ], \json_encode(['foo' => 'bar']));

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "class": "PSX.Data.Body.Json",
    "raw": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testMultipart()
    {
        $this->markTestSkipped();

        $_FILES['foo'] = [
            'name' => 'bar.txt',
            'type' => 'text/plain',
            'size' => 1337,
            'tmp_name' => 'tmp.txt',
            'error' => 0,
        ];

        $response = $this->sendRequest('/mime/multipart', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873',
            'Content-Type' => 'multipart/form-data'
        ], \json_encode(['foo' => 'bar']));

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "class": "PSX.Data.Body.Multipart",
    "raw": {
        "foo": {
            "name": "bar.txt",
            "type": "text\/plain",
            "size": 1337,
            "tmp_name": "tmp.txt",
            "error": 0
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testText()
    {
        $response = $this->sendRequest('/mime/text', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873',
            'Content-Type' => 'text/plain'
        ], 'foobar');

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "class": "string",
    "raw": "foobar"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testXml()
    {
        $response = $this->sendRequest('/mime/xml', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873',
            'Content-Type' => 'application/xml'
        ], '<root><foo>bar</foo></root>');

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "class": "DOMDocument",
    "raw": "<?xml version=\"1.0\"?>\n<root><foo>bar<\/foo><\/root>\n"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}
