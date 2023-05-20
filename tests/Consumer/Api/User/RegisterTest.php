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
use PSX\Framework\Test\Environment;

/**
 * RegisterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class RegisterTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

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
            ->select('provider', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(1, $row['provider']);
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
