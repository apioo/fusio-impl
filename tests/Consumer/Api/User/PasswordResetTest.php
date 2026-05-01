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

namespace Fusio\Impl\Tests\Consumer\Api\User;

use Fusio\Impl\Tests\DbTestCase;

/**
 * PasswordResetTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class PasswordResetTest extends DbTestCase
{
    public function testGet(): void
    {
        $response = $this->sendRequest('/consumer/password_reset', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
        ]);

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost(): void
    {
        $response = $this->sendRequest('/consumer/password_reset', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
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

    public function testPostInvalidEmail(): void
    {
        $response = $this->sendRequest('/consumer/password_reset', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
            'email' => 'baz',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertEquals('Could not find user', substr((string) $data['message'], 0, 19), $body);
    }

    public function testPostNoEmail(): void
    {
        $response = $this->sendRequest('/consumer/password_reset', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar'
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertStringStartsWith('No email was provided', $data['message'], $body);
    }

    public function testPut(): void
    {
        // set token
        $this->testPost();

        $token = $this->connection->fetchOne('SELECT token FROM fusio_user WHERE id = :id', ['id' => 2]);

        $response = $this->sendRequest('/consumer/password_reset', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
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
        $this->assertTrue(password_verify('foo', (string) $user['password']));
        $this->assertEmpty($user['token']);
    }

    public function testPutInvalidToken(): void
    {
        $response = $this->sendRequest('/consumer/password_reset', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
            'token' => 'foobar',
            'newPassword' => 'foo',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Invalid token provided', substr((string) $data['message'], 0, 22), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/consumer/password_reset', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
