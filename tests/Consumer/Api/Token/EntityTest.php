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

namespace Fusio\Impl\Tests\Consumer\Api\Token;

use Fusio\Impl\Table;
use Fusio\Impl\Table\Token;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Sql\TableManagerInterface;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/token/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $data = json_decode($body);
        $sec  = $data->appSecret ?? null;
        $body = str_replace(trim(json_encode($sec), '"'), '[app_secret]', $body);

        $expect = <<<'JSON'
{
    "id": 2,
    "status": 1,
    "scope": [
        "consumer",
        "authorization"
    ],
    "ip": "127.0.0.1",
    "expire": "[datetime]",
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/token/2', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/token/2', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'name' => 'baz',
            'expire' => (new \DateTime())->add(new \DateInterval('P4D'))->format(\DateTimeInterface::RFC3339),
            'scopes' => ['foo', 'bar'], // scopes can not change on update
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertNotEmpty($data->access_token);
        $this->assertEquals('bearer', $data->token_type);
        $this->assertEquals(345600, $data->expires_in);
        $this->assertNotEmpty($data->refresh_token);
        $this->assertEquals('consumer,authorization', $data->scope);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'app_id', 'user_id', 'name', 'token', 'refresh', 'scope')
            ->from('fusio_token')
            ->where('id = 2')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        // the original token gets deleted
        $this->assertEquals(2, $row['id']);
        $this->assertEquals(Token::STATUS_DELETED, $row['status']);

        // and we a new token was created
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'app_id', 'user_id', 'name', 'token', 'refresh', 'scope')
            ->from('fusio_token')
            ->where('token = :token')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['token' => $data->access_token]);

        $this->assertEquals(7, $row['id']);
        $this->assertEquals(Token::STATUS_ACTIVE, $row['status']);
        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(1, $row['user_id']);
        $this->assertEquals('baz', $row['name']);
        $this->assertNotEmpty($row['token']);
        $this->assertNotEmpty($row['refresh']);
        $this->assertEquals('consumer,authorization', $row['scope']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/token/2', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Token successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_token')
            ->where('id = 2')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(2, $row['id']);
        $this->assertEquals(Table\Token::STATUS_DELETED, $row['status']);
    }
}
