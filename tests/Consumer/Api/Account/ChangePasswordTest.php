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

namespace Fusio\Impl\Tests\Consumer\Account;

use Firebase\JWT\JWT;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * ChangePasswordTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ChangePasswordTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/account/change_password', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer 1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/account/change_password', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer 1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/account/change_password', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer 1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b'
        ), json_encode([
            'oldPassword'    => 'qf2vX10Ec3wFZHx0K1eL',
            'newPassword'    => 'qf2vX10Ec4wFZHx0K1eL',
            'verifyPassword' => 'qf2vX10Ec4wFZHx0K1eL',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "success": true,
    "message": "Password successful changed"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database password
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();
        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => 2]);

        $this->assertTrue(password_verify('qf2vX10Ec4wFZHx0K1eL', $row['password']));
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/account/change_password', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer 1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
