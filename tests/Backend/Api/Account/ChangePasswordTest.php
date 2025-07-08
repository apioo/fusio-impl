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

namespace Fusio\Impl\Tests\Backend\Api\Account;

use Fusio\Impl\Tests\DbTestCase;

/**
 * ChangePasswordTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ChangePasswordTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/account/change_password', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/account/change_password', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/account/change_password', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ), json_encode([
            'oldPassword'    => 'qf2vX10Ec3wFZHx0K1eL',
            'newPassword'    => 'qf2vX10Ec4wFZHx0K1eL!',
            'verifyPassword' => 'qf2vX10Ec4wFZHx0K1eL!',
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
        $sql = $this->connection->createQueryBuilder()
            ->select('password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();
        $row = $this->connection->fetchAssociative($sql, ['id' => 4]);

        $this->assertTrue(password_verify('qf2vX10Ec4wFZHx0K1eL!', $row['password']));
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/account/change_password', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
