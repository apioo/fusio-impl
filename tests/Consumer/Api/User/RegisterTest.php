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

namespace Fusio\Impl\Tests\Consumer\Api\User;

use Fusio\Impl\Tests\DbTestCase;

/**
 * RegisterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RegisterTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/consumer/register', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/register', 'POST', array(
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
        $sql = $this->connection->createQueryBuilder()
            ->select('identity_id', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(null, $row['identity_id']);
        $this->assertEquals(2, $row['status']);
        $this->assertEquals('', $row['remote_id']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);
    }

    public function testPostInvalidEmail()
    {
        $response = $this->sendRequest('/consumer/register', 'POST', array(
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
        $response = $this->sendRequest('/consumer/register', 'POST', array(
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

    public function testPostInvalidPasswordCharacters()
    {
        $response = $this->sendRequest('/consumer/register', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'name'     => 'baz',
            'email'    => 'baz@bar.com',
            'password' => 'foobar foobar',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Password must contain only printable ascii characters', substr($data['message'], 0, 53), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/register', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/register', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
