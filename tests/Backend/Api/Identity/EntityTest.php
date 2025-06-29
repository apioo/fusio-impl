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

namespace Fusio\Impl\Tests\Backend\Api\Identity;

use Fusio\Impl\Provider\Identity\Github;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Fixture;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends DbTestCase
{
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getReference('fusio_identity', 'GitHub')->resolve($this->connection);
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
    "id": 2,
    "roleId": 3,
    "appId": 2,
    "status": 1,
    "name": "GitHub",
    "icon": "bi-github",
    "class": "Fusio\\Impl\\Provider\\Identity\\Github",
    "config": {
        "client_id": "github-key",
        "client_secret": "github-secret",
        "authorization_uri": "https:\/\/github.com\/login\/oauth\/authorize",
        "token_uri": "https:\/\/github.com\/login\/oauth\/access_token",
        "user_info_uri": "https:\/\/api.github.com\/user",
        "id_property": "id",
        "name_property": "login",
        "email_property": "email"
    },
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
    "id": 2,
    "roleId": 3,
    "appId": 2,
    "status": 1,
    "name": "GitHub",
    "icon": "bi-github",
    "class": "Fusio\\Impl\\Provider\\Identity\\Github",
    "config": {
        "client_id": "github-key",
        "client_secret": "github-secret",
        "authorization_uri": "https:\/\/github.com\/login\/oauth\/authorize",
        "token_uri": "https:\/\/github.com\/login\/oauth\/access_token",
        "user_info_uri": "https:\/\/api.github.com\/user",
        "id_property": "id",
        "name_property": "login",
        "email_property": "email"
    },
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
            'config' => [
                'client_id' => 'bar',
                'client_secret' => 'foo',
                'authorization_uri' => 'https://github.com/login/oauth/authorize',
                'token_uri' => 'https://github.com/login/oauth/access_token',
                'user_info_uri' => 'https://api.github.com/user',
                'id_property' => 'id',
                'name_property' => 'foo',
                'email_property' => 'email',
            ]
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Identity successfully updated",
    "id": "2"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'app_id', 'role_id', 'status', 'name', 'icon', 'class', 'config', 'allow_create')
            ->from('fusio_identity')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(2, $row['id']);
        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(3, $row['role_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('GitGit', $row['name']);
        $this->assertEquals('bi-github', $row['icon']);
        $this->assertEquals(Github::class, $row['class']);
        $this->assertEquals([
            'client_id' => 'bar',
            'client_secret' => 'foo',
            'authorization_uri' => 'https://github.com/login/oauth/authorize',
            'token_uri' => 'https://github.com/login/oauth/access_token',
            'user_info_uri' => 'https://api.github.com/user',
            'id_property' => 'id',
            'name_property' => 'foo',
            'email_property' => 'email',
        ], \json_decode($row['config'], true));
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
    "message": "Identity successfully deleted",
    "id": "2"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('fusio_identity')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals($this->id, $row['id']);
    }
}
