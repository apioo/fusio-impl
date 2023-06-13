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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Impl\Table\App\Token;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Http\ResponseInterface;
use PSX\Json\Parser;

/**
 * PasswordTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class PasswordTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testPost()
    {
        $body     = 'grant_type=password&username=Developer&password=qf2vX10Ec3wFZHx0K1eL&scope=authorization,backend';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('5347307d-d801-4075-9aaa-a21a29a448c5:342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $this->assertAccessToken($response);
    }

    public function testPostEmail()
    {
        $body     = 'grant_type=password&username=developer@localhost.com&password=qf2vX10Ec3wFZHx0K1eL&scope=authorization,backend';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('5347307d-d801-4075-9aaa-a21a29a448c5:342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $this->assertAccessToken($response);
    }

    /**
     * A pending app can not request an API token
     */
    public function testPostPending()
    {
        $body     = 'grant_type=password&username=Developer&password=qf2vX10Ec3wFZHx0K1eL&scope=authorization,backend';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('7c14809c-544b-43bd-9002-23e1c2de6067:bb0574181eb4a1326374779fe33e90e2c427f28ab0fc1ffd168bfd5309ee7caa'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $body = (string) $response->getBody();
        
        $expect = <<<JSON
{
    "error": "invalid_client",
    "error_description": "Unknown credentials"
}
JSON;

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    /**
     * A deactivated app can not request an API token
     */
    public function testPostDeactivated()
    {
        $body     = 'grant_type=password&username=Developer&password=qf2vX10Ec3wFZHx0K1eL&scope=authorization,backend';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('f46af464-f7eb-4d04-8661-13063a30826b:17b882987298831a3af9c852f9cd0219d349ba61fcf3fc655ac0f07eece951f9'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $body = (string) $response->getBody();
        
        $expect = <<<JSON
{
    "error": "invalid_client",
    "error_description": "Unknown credentials"
}
JSON;

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    private function assertAccessToken(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        $data = Parser::decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);

        $expireDate = strtotime('+2 day');

        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertEquals(date('Y-m-d H:i', $expireDate), date('Y-m-d H:i', $data['expires_in']));
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertArrayHasKey('scope', $data);
        $this->assertEquals('authorization', $data['scope']);

        // check whether the token was created
        $row = $this->connection->fetchAssociative('SELECT app_id, user_id, status, token, refresh, scope, expire, date FROM fusio_app_token WHERE token = :token', ['token' => $data['access_token']]);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(4, $row['user_id']);
        $this->assertEquals(Token::STATUS_ACTIVE, $row['status']);
        $this->assertEquals($data['access_token'], $row['token']);
        $this->assertEquals($data['refresh_token'], $row['refresh']);
        $this->assertEquals('authorization', $row['scope']);
        $this->assertEquals(date('Y-m-d H:i', $expireDate), date('Y-m-d H:i', strtotime($row['expire'])));
        $this->assertEquals(date('Y-m-d H:i'), substr($row['date'], 0, 16));
    }
}
