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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Impl\Table\Token;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Json\Parser;

/**
 * AuthorizationCodeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuthorizationCodeTest extends DbTestCase
{
    public function testPost()
    {
        $body     = 'grant_type=authorization_code&code=GHMbtJi0ZuAUnp80';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('5347307d-d801-4075-9aaa-a21a29a448c5:342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $body = (string) $response->getBody();
        $data = Parser::decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);

        $expireDate = strtotime('+2 day');

        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertEquals(172800, $data['expires_in']);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertArrayHasKey('scope', $data);
        $this->assertEquals('authorization', $data['scope']);
        $this->assertArrayNotHasKey('id_token', $data);

        // check whether the token was created
        $row = $this->connection->fetchAssociative('SELECT app_id, user_id, status, token, refresh, scope, expire, date FROM fusio_token WHERE token = :token', ['token' => $data['access_token']]);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(Token::STATUS_ACTIVE, $row['status']);
        $this->assertEquals($data['access_token'], $row['token']);
        $this->assertEquals($data['refresh_token'], $row['refresh']);
        $this->assertEquals('authorization', $row['scope']);
        $this->assertEquals(date('Y-m-d H', $expireDate), date('Y-m-d H', strtotime($row['expire'])));
        $this->assertEquals(date('Y-m-d H'), substr($row['date'], 0, 13));
    }

    public function testPostIDToken()
    {
        $body     = 'grant_type=authorization_code&code=108742516c3fdf32';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('5347307d-d801-4075-9aaa-a21a29a448c5:342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $body = (string) $response->getBody();
        $data = Parser::decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);

        $expireDate = strtotime('+2 day');

        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertEquals(172800, $data['expires_in']);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertArrayHasKey('scope', $data);
        $this->assertEquals('openid', $data['scope']);
        $this->assertArrayHasKey('id_token', $data);

        // check whether the token was created
        $row = $this->connection->fetchAssociative('SELECT app_id, user_id, status, token, refresh, scope, expire, date FROM fusio_token WHERE token = :token', ['token' => $data['access_token']]);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(Token::STATUS_ACTIVE, $row['status']);
        $this->assertEquals($data['access_token'], $row['token']);
        $this->assertEquals($data['refresh_token'], $row['refresh']);
        $this->assertEquals('openid', $row['scope']);
        $this->assertEquals(date('Y-m-d H', $expireDate), date('Y-m-d H', strtotime($row['expire'])));
        $this->assertEquals(date('Y-m-d H'), substr($row['date'], 0, 13));
    }

    /**
     * A pending app can not request an API token
     */
    public function testPostPending()
    {
        $body     = 'grant_type=authorization_code&code=GHMbtJi0ZuAUnp80';
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
     * A pending app can not request an API token
     */
    public function testPostDeactivated()
    {
        $body     = 'grant_type=authorization_code&code=GHMbtJi0ZuAUnp80';
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
}
