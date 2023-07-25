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

use Fusio\Impl\Provider\Identity\Google;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/identity', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 1,
            "status": 1,
            "name": "Facebook",
            "icon": "bi-facebook",
            "class": "Fusio\\Impl\\Provider\\Identity\\Facebook",
            "insertDate": "2023-07-22T13:56:00Z"
        },
        {
            "id": 2,
            "status": 1,
            "name": "GitHub",
            "icon": "bi-github",
            "class": "Fusio\\Impl\\Provider\\Identity\\Github",
            "insertDate": "2023-07-22T13:56:00Z"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Google",
            "icon": "bi-google",
            "class": "Fusio\\Impl\\Provider\\Identity\\Google",
            "insertDate": "2023-07-22T13:56:00Z"
        },
        {
            "id": 4,
            "status": 1,
            "name": "OpenID",
            "icon": "bi-openid",
            "class": "Fusio\\Impl\\Provider\\Identity\\OpenIDConnect",
            "insertDate": "2023-07-22T13:56:00Z"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/identity', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'appId' => 1,
            'name' => 'NewIdentity',
            'icon' => 'bi-google',
            'class' => Google::class,
            'config' => [
                'client_id' => 'foo',
                'client_secret' => 'bar',
                'authorization_uri' => 'https://github.com/login/oauth/authorize',
                'token_uri' => 'https://github.com/login/oauth/access_token',
                'user_info_uri' => 'https://api.github.com/user',
                'id_property' => 'id',
                'name_property' => 'login',
                'email_property' => 'email',
            ]
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Identity successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'app_id', 'role_id', 'status', 'name', 'icon', 'class', 'config', 'allow_create')
            ->from('fusio_identity')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(5, $row['id']);
        $this->assertEquals(1, $row['app_id']);
        $this->assertEquals(null, $row['role_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('NewIdentity', $row['name']);
        $this->assertEquals('bi-google', $row['icon']);
        $this->assertEquals('Fusio.Impl.Provider.Identity.Google', $row['class']);
        $this->assertEquals([
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'authorization_uri' => 'https://github.com/login/oauth/authorize',
            'token_uri' => 'https://github.com/login/oauth/access_token',
            'user_info_uri' => 'https://api.github.com/user',
            'id_property' => 'id',
            'name_property' => 'login',
            'email_property' => 'email',
        ], \json_decode($row['config'], true));
        $this->assertEquals(1, $row['allow_create']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/identity', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/identity', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
