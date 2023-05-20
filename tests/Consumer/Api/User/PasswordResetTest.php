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

namespace Fusio\Impl\Tests\Consumer\Api\User;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * PasswordResetTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class PasswordResetTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/password_reset', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/password_reset', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'email' => 'consumer@localhost.com',
        ]));

        $actual = (string) $response->getBody();
        $expect = <<<JSON
{
    "success": true,
    "message": "Password reset email was send"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);

        $token = $this->connection->fetchOne('SELECT token FROM fusio_user WHERE id = 2');
        $this->assertNotEmpty($token);
    }

    public function testPostInvalidEmail()
    {
        $response = $this->sendRequest('/consumer/password_reset', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'email' => 'baz',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertEquals('Could not find user', substr($data['message'], 0, 19), $body);
    }

    public function testPostNoEmail()
    {
        $response = $this->sendRequest('/consumer/password_reset', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar'
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('/ the following properties are required: email', substr($data['message'], 0, 46), $body);
    }

    public function testPut()
    {
        // set token
        $this->testPost();

        $token = $this->connection->fetchOne('SELECT token FROM fusio_user WHERE id = :id', ['id' => 2]);

        $response = $this->sendRequest('/consumer/password_reset', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'token' => $token,
            'newPassword' => 'foo',
        ]));

        $actual = (string) $response->getBody();
        $expect = <<<JSON
{
    "success": true,
    "message": "Password successful changed"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);

        // verify password
        $user = $this->connection->fetchAssociative('SELECT password, token FROM fusio_user WHERE id = :id', ['id' => 2]);
        $this->assertTrue(password_verify('foo', $user['password']));
        $this->assertEmpty($user['token']);
    }

    public function testPutInvalidToken()
    {
        $response = $this->sendRequest('/consumer/password_reset', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'token' => 'foobar',
            'newPassword' => 'foo',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Invalid token provided', substr($data['message'], 0, 22), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/password_reset', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
