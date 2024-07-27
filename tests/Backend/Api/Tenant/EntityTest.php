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

namespace Fusio\Impl\Tests\Backend\Api\Tenant;

use Fusio\Impl\Tests\Adapter\Test\InspectAction;
use Fusio\Impl\Tests\Adapter\Test\PaypalConnection;
use Fusio\Impl\Tests\DbTestCase;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends DbTestCase
{
    private const TENANT_ID = 'customer_a';

    public function testGet()
    {
        $response = $this->sendRequest('/backend/tenant/' . self::TENANT_ID, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/tenant/' . self::TENANT_ID, 'POST', array(
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
        $response = $this->sendRequest('/backend/tenant/' . self::TENANT_ID, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertTrue($data->success, $body);
    }

    public function testDelete()
    {
        // create a tenant before we delete the data
        $response = $this->sendRequest('/backend/tenant/' . self::TENANT_ID, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertTrue($data->success, $body);

        // insert resources
        $this->connection->insert('fusio_action', ['tenant_id' => self::TENANT_ID, 'category_id' => 1, 'status' => 1, 'name' => 'Inspect-Action', 'class' => InspectAction::class, 'date' => '2024-03-16 13:57:39']);
        $this->connection->insert('fusio_connection', ['tenant_id' => self::TENANT_ID, 'status' => 1, 'name' => 'Paypal', 'class' => PaypalConnection::class]);

        // now delete the tenant
        $response = $this->sendRequest('/backend/tenant/' . self::TENANT_ID, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertTrue($data->success);
    }
}
