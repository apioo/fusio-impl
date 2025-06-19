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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Tests\DbTestCase;
use PSX\Json\Parser;

/**
 * FirewallTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class FirewallTest extends DbTestCase
{
    public function testIPBan()
    {
        // we send so many requests until we hit the rate limit and get an IP ban
        for ($i = 0; $i < 43; $i++) {
            $response = $this->sendRequest('/foo', 'GET', [
                'User-Agent' => 'Fusio TestCase',
            ]);
        }

        $body = (string) $response->getBody();
        $data = Parser::decode($body);

        $this->assertEquals(403, $response->getStatusCode(), $body);
        $this->assertEquals(false, $data->success, $body);
        $this->assertEquals('Your IP has sent to many requests please try again later', substr($data->message, 0, 56), $body);

        $now = new \DateTime();
        $now->add(new \DateInterval('PT5M'));

        $row = $this->connection->fetchAssociative('SELECT name, type, ip, expire FROM fusio_firewall WHERE name LIKE :name', ['name' => 'Ban%']);
        $this->assertNotEmpty($row);
        $this->assertEquals('Ban-127-0-0-1', substr($row['name'], 0, 13));
        $this->assertEquals(0, $row['type']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertEquals($now->format('Y-m-d H:i'), substr($row['expire'], 0, 16));
    }

    public function testIPBanAuthorization()
    {
        // we send so many requests until we hit the rate limit and get an IP ban
        for ($i = 0; $i < 35; $i++) {
            $response = $this->sendRequest('/authorization/token', 'POST', [
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Basic ' . base64_encode('brute:force'),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ], 'grant_type=client_credentials');
        }

        $body = (string) $response->getBody();
        $data = Parser::decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('invalid_request', $data->error, $body);
        $this->assertEquals('Your IP has sent to many requests please try again later', $data->error_description, $body);

        $now = new \DateTime();
        $now->add(new \DateInterval('PT5M'));

        $row = $this->connection->fetchAssociative('SELECT name, type, ip, expire FROM fusio_firewall WHERE name LIKE :name', ['name' => 'Ban%']);
        $this->assertNotEmpty($row);
        $this->assertEquals('Ban-127-0-0-1', substr($row['name'], 0, 13));
        $this->assertEquals(0, $row['type']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertEquals($now->format('Y-m-d H:i'), substr($row['expire'], 0, 16));
    }
}
