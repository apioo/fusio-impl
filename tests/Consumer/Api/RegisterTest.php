<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Tests\Consumer;

use Firebase\JWT\JWT;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * RegisterTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RegisterTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'name'     => 'baz',
            'email'    => 'baz@bar.com',
            'password' => 'foobar!123',
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);
        $token = JWT::decode($data->token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals(6, $token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertNotEmpty($token->jti);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('appId', 'userId', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(1, $row['appId']);
        $this->assertEquals(6, $row['userId']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals($row['token'], $token->jti);
        $this->assertEquals(Environment::getConfig()->get('fusio_scopes_default'), $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);
    }

    public function testPostInvalidEmail()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/register', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
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
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
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
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
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
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
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
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
