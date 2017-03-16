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

namespace Fusio\Impl\Tests\Consumer\User;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * RegisterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RegisterTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('http://127.0.0.1/doc/*/consumer/register', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/consumer\/register",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "User": {
                "type": "object",
                "title": "user",
                "properties": {
                    "name": {
                        "type": "string"
                    },
                    "email": {
                        "type": "string"
                    },
                    "password": {
                        "type": "string"
                    },
                    "captcha": {
                        "type": "string"
                    }
                },
                "required": [
                    "name",
                    "email",
                    "password"
                ]
            },
            "Message": {
                "type": "object",
                "title": "message",
                "properties": {
                    "success": {
                        "type": "boolean"
                    },
                    "message": {
                        "type": "string"
                    }
                }
            },
            "POST-request": {
                "$ref": "#\/definitions\/User"
            },
            "POST-200-response": {
                "$ref": "#\/definitions\/Message"
            }
        }
    },
    "methods": {
        "POST": {
            "request": "#\/definitions\/POST-request",
            "responses": {
                "200": "#\/definitions\/POST-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/consumer\/register"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/consumer\/register"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'name'     => 'baz',
            'email'    => 'baz@localhost.com',
            'password' => 'foobar!123',
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals(true, $data->success);
        $this->assertEquals('Registration successful', $data->message);

        // check database user
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('provider', 'status', 'remoteId', 'name', 'email')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(1, $row['provider']);
        $this->assertEquals(2, $row['status']);
        $this->assertEquals('', $row['remoteId']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);
    }

    public function testPostInvalidEmail()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'name'     => 'baz',
            'email'    => 'baz',
            'password' => 'foo!12',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Invalid email format', substr($data['message'], 0, 20), $body);
    }

    public function testPostInvalidPasswordLength()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'name'     => 'baz',
            'email'    => 'baz@bar.com',
            'password' => 'foo!12',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Password must have at least 8 characters', substr($data['message'], 0, 40), $body);
    }

    public function testPostInvalidPasswordComplexity()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'name'     => 'baz',
            'email'    => 'baz@bar.com',
            'password' => 'foobarfoobar',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Password must have at least one numeric character (0-9)', substr($data['message'], 0, 55), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
