<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Identity;

use Fusio\Impl\Provider\User\OpenIDConnect;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getId('fusio_identity', 'GitHub');
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/identity/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "id": 1,
    "status": 1,
    "name": "GitHub",
    "icon": "bi-github",
    "class": "Fusio\\Impl\\Provider\\User\\OpenIDConnect",
    "clientId": "foo",
    "clientSecret": "bar",
    "authorizationUri": "http:\/\/127.0.0.1\/authorization",
    "tokenUri": "http:\/\/127.0.0.1\/token",
    "userInfoUri": "http:\/\/127.0.0.1\/authorization\/whoami",
    "idProperty": "id",
    "nameProperty": "name",
    "emailProperty": "email",
    "allowCreate": true,
    "insertDate": "2023-07-22T13:56:00Z"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/identity/~GitHub', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "id": 1,
    "status": 1,
    "name": "GitHub",
    "icon": "bi-github",
    "class": "Fusio\\Impl\\Provider\\User\\OpenIDConnect",
    "clientId": "foo",
    "clientSecret": "bar",
    "authorizationUri": "http:\/\/127.0.0.1\/authorization",
    "tokenUri": "http:\/\/127.0.0.1\/token",
    "userInfoUri": "http:\/\/127.0.0.1\/authorization\/whoami",
    "idProperty": "id",
    "nameProperty": "name",
    "emailProperty": "email",
    "allowCreate": true,
    "insertDate": "2023-07-22T13:56:00Z"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/identity/370', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find identity', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/identity/' . $this->id, 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/identity/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name' => 'GitGit',
            'clientId' => 'bar',
            'clientSecret' => 'foo',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Identity successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'app_id', 'role_id', 'status', 'name', 'icon', 'class', 'client_id', 'client_secret', 'authorization_uri', 'token_uri', 'user_info_uri', 'id_property', 'name_property', 'email_property', 'allow_create')
            ->from('fusio_identity')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(1, $row['id']);
        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(3, $row['role_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('GitGit', $row['name']);
        $this->assertEquals('bi-github', $row['icon']);
        $this->assertEquals(OpenIDConnect::class, $row['class']);
        $this->assertEquals('bar', $row['client_id']);
        $this->assertEquals('foo', $row['client_secret']);
        $this->assertEquals('http://127.0.0.1/authorization', $row['authorization_uri']);
        $this->assertEquals('http://127.0.0.1/token', $row['token_uri']);
        $this->assertEquals('http://127.0.0.1/authorization/whoami', $row['user_info_uri']);
        $this->assertEquals('id', $row['id_property']);
        $this->assertEquals('name', $row['name_property']);
        $this->assertEquals('email', $row['email_property']);
        $this->assertEquals(1, $row['allow_create']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/identity/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Identity successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('fusio_identity')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals($this->id, $row['id']);
    }
}
