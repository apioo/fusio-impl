<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Impl\Tests\Backend\Api\Operation;

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Api\OperationInterface;
use PSX\Json\Parser;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/operation', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 14,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 268,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/xml",
            "httpCode": 200,
            "name": "mime.xml",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 267,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/text",
            "httpCode": 200,
            "name": "mime.text",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 266,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/multipart",
            "httpCode": 200,
            "name": "mime.multipart",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 265,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/json",
            "httpCode": 200,
            "name": "mime.json",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 264,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/form",
            "httpCode": 200,
            "name": "mime.form",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 263,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/binary",
            "httpCode": 200,
            "name": "mime.binary",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 262,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "DELETE",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.delete",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 261,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "PATCH",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.patch",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 260,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "PUT",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.put",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 259,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.post",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 258,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.get",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 257,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/foo",
            "httpCode": 201,
            "name": "test.createFoo",
            "action": "action:\/\/Sql-Insert"
        },
        {
            "id": 256,
            "status": 1,
            "active": true,
            "public": true,
            "stability": 1,
            "httpMethod": "GET",
            "httpPath": "\/foo",
            "httpCode": 200,
            "name": "test.listFoo",
            "action": "action:\/\/Sql-Select-All"
        },
        {
            "id": 1,
            "status": 1,
            "active": true,
            "public": true,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/",
            "httpCode": 200,
            "name": "meta.getAbout",
            "action": "php+class:\/\/Fusio.Impl.System.Action.Meta.GetAbout"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/operation?search=inspec', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 5,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 262,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "DELETE",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.delete",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 261,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "PATCH",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.patch",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 260,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "PUT",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.put",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 259,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.post",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 258,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.get",
            "action": "action:\/\/Inspect-Action"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/operation?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 14,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 268,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/xml",
            "httpCode": 200,
            "name": "mime.xml",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 267,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/text",
            "httpCode": 200,
            "name": "mime.text",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 266,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/multipart",
            "httpCode": 200,
            "name": "mime.multipart",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 265,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/json",
            "httpCode": 200,
            "name": "mime.json",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 264,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/form",
            "httpCode": 200,
            "name": "mime.form",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 263,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/mime\/binary",
            "httpCode": 200,
            "name": "mime.binary",
            "action": "action:\/\/MIME-Action"
        },
        {
            "id": 262,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "DELETE",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.delete",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 261,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "PATCH",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.patch",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 260,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "PUT",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.put",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 259,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.post",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 258,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/inspect\/:foo",
            "httpCode": 200,
            "name": "inspect.get",
            "action": "action:\/\/Inspect-Action"
        },
        {
            "id": 257,
            "status": 1,
            "active": true,
            "public": false,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/foo",
            "httpCode": 201,
            "name": "test.createFoo",
            "action": "action:\/\/Sql-Insert"
        },
        {
            "id": 256,
            "status": 1,
            "active": true,
            "public": true,
            "stability": 1,
            "httpMethod": "GET",
            "httpPath": "\/foo",
            "httpCode": 200,
            "name": "test.listFoo",
            "action": "action:\/\/Sql-Select-All"
        },
        {
            "id": 1,
            "status": 1,
            "active": true,
            "public": true,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/",
            "httpCode": 200,
            "name": "meta.getAbout",
            "action": "php+class:\/\/Fusio.Impl.System.Action.Meta.GetAbout"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'parameters' => [
                'foo' => [
                    'type' => 'string'
                ]
            ],
            'incoming'   => 'Passthru',
            'outgoing'   => 'Passthru',
            'throws'     => [
                500 => 'Passthru',
            ],
            'cost'       => 10,
            'action'     => 'Sql-Select-All',
            'scopes'     => ['foo', 'baz'],
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully created",
    "id": "269"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertOperation($this->connection, OperationInterface::STABILITY_EXPERIMENTAL, 'test.bar', 'GET', '/foo/bar', 200, ['foo', 'baz'], $metadata);
    }

    public function testPostWithScheme()
    {
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'parameters' => [
                'foo' => [
                    'type' => 'string'
                ]
            ],
            'incoming'   => 'schema://Passthru',
            'outgoing'   => 'schema://Passthru',
            'throws'     => [
                500 => 'schema://Passthru',
            ],
            'cost'       => 10,
            'action'     => 'action://Sql-Select-All',
            'scopes'     => ['foo', 'baz'],
            'metadata' => $metadata,
        ]));

        $body = (string) $response->getBody();
        $data = Parser::decode($body);

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertSame(true, $data->success);
        $this->assertSame('Operation successfully created', $data->message);
        $this->assertContains($data->id, ['268', '269']); // postgres does not reset the auto increment so we need to check both

        // check database
        Assert::assertOperation($this->connection, OperationInterface::STABILITY_EXPERIMENTAL, 'test.bar', 'GET', '/foo/bar', 200, ['foo', 'baz'], $metadata);
    }

    public function testPostStabilityInvalid()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => 99,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Stability contain an invalid value must be one of: 0, 1, 2, 3', $data->message);
    }

    public function testPostHttpMethodInvalid()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'FOO',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('HTTP method must not be one of: GET, POST, PUT, PATCH, DELETE', $data->message);
    }

    public function testPostHttpPathInvalid()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => 'foobar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('HTTP path must start with a /', $data->message);
    }

    public function testPostHttpCodeInvalid()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo',
            'httpCode'   => 999,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('HTTP code contains an HTTP status code "999" which is not in the range between 200 and 299', $data->message);
    }

    public function testPostHttpMethodAndPathExisting()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('An operation exists already with the same HTTP method and path', $data->message);
    }

    public function testPostNameInvalid()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'foo&bar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Invalid operation name', $data->message);
    }

    public function testPostParametersInvalidName()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'parameters' => [
                'fo&o' => [
                    'type' => 'string'
                ]
            ],
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Parameter name "fo&o" contains an invalid character, allowed are only alphanumeric characters and underscore', $data->message);
    }

    public function testPostParametersInvalidSchema()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'parameters' => [
                'foo' => [
                    'type' => 'foobar'
                ]
            ],
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Parameter "foo" contains an invalid schema "foobar" must be one of: string, boolean, integer, number', $data->message);
    }

    public function testPostIncomingNonExistingSchema()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'incoming'   => 'Foobar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Incoming schema "Foobar" does not exist', $data->message);
    }

    public function testPostIncomingNonExistingSchemaWithScheme()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'incoming'   => 'schema://Foobar',
            'outgoing'   => 'Passthru',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Incoming schema "schema://Foobar" does not exist', $data->message);
    }

    public function testPostOutgoingNonExistingSchema()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Foobar',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Outgoing schema "Foobar" does not exist', $data->message);
    }

    public function testPostOutgoingNonExistingSchemaWithScheme()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'schema://Foobar',
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Outgoing schema "schema://Foobar" does not exist', $data->message);
    }

    public function testPostThrowNonExistingSchema()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'throws'     => [
                500 => 'Foobar',
            ],
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Throw 500 schema "Foobar" does not exist', $data->message);
    }

    public function testPostThrowNonExistingSchemaWithScheme()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'throws'     => [
                500 => 'schema://Foobar',
            ],
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Throw 500 schema "schema://Foobar" does not exist', $data->message);
    }

    public function testPostThrowInvalidStatusCode()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'throws'     => [
                900 => 'Passthru',
            ],
            'action'     => 'Sql-Select-All',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Throw contains an HTTP status code "900" which is not in the range between 400 and 599', $data->message);
    }

    public function testPostActionNonExisting()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'action'     => 'Foobar',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Action "Foobar" does not exist', $data->message);
    }

    public function testPostActionNonExistingWithScheme()
    {
        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_EXPERIMENTAL,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/bar',
            'httpCode'   => 200,
            'name'       => 'test.bar',
            'outgoing'   => 'Passthru',
            'action'     => 'action://Foobar',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Action "action://Foobar" does not exist', $data->message);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/operation', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/operation', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
