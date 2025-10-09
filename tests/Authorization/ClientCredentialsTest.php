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
use PSX\Http\ResponseInterface;
use PSX\Json\Parser;

/**
 * ClientCredentialsTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ClientCredentialsTest extends DbTestCase
{
    public function testPost()
    {
        $body     = 'grant_type=client_credentials';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('Developer:qf2vX10Ec3wFZHx0K1eL'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        // if we provide no explicit scopes we get all scopes assigned to the user
        $this->assertAccessToken($response, 'backend,backend.account,backend.action,backend.app,backend.audit,backend.backup,backend.category,backend.config,backend.connection,backend.cronjob,backend.dashboard,backend.event,backend.firewall,backend.form,backend.generator,backend.identity,backend.log,backend.marketplace,backend.operation,backend.page,backend.plan,backend.rate,backend.role,backend.schema,backend.scope,backend.sdk,backend.statistic,backend.tenant,backend.test,backend.token,backend.transaction,backend.trash,backend.trigger,backend.user,backend.webhook,consumer,consumer.account,consumer.app,consumer.event,consumer.form,consumer.grant,consumer.identity,consumer.log,consumer.page,consumer.payment,consumer.plan,consumer.scope,consumer.token,consumer.transaction,consumer.webhook,authorization,foo,bar', 4);
    }

    public function testPostSpecificScope()
    {
        $body     = 'grant_type=client_credentials&scope=backend.action';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('Developer:qf2vX10Ec3wFZHx0K1eL'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $this->assertAccessToken($response, 'backend.action', 4);
    }

    public function testPostEmail()
    {
        $body     = 'grant_type=client_credentials';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('developer@localhost.com:qf2vX10Ec3wFZHx0K1eL'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        $this->assertAccessToken($response, 'backend,backend.account,backend.action,backend.app,backend.audit,backend.backup,backend.category,backend.config,backend.connection,backend.cronjob,backend.dashboard,backend.event,backend.firewall,backend.form,backend.generator,backend.identity,backend.log,backend.marketplace,backend.operation,backend.page,backend.plan,backend.rate,backend.role,backend.schema,backend.scope,backend.sdk,backend.statistic,backend.tenant,backend.test,backend.token,backend.transaction,backend.trash,backend.trigger,backend.user,backend.webhook,consumer,consumer.account,consumer.app,consumer.event,consumer.form,consumer.grant,consumer.identity,consumer.log,consumer.page,consumer.payment,consumer.plan,consumer.scope,consumer.token,consumer.transaction,consumer.webhook,authorization,foo,bar', 4);
    }

    /**
     * As consumer we can not request an backend token
     */
    public function testPostConsumer()
    {
        $body     = 'grant_type=client_credentials';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('Consumer:qf2vX10Ec3wFZHx0K1eL'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        // we receive only the authorization scope since out user has not the backend scope
        $this->assertAccessToken($response, 'consumer,consumer.account,consumer.app,consumer.event,consumer.form,consumer.grant,consumer.identity,consumer.log,consumer.page,consumer.payment,consumer.plan,consumer.scope,consumer.token,consumer.transaction,consumer.webhook,authorization,openid,foo,bar', 2);
    }

    /**
     * A deactivated user can not request a backend token
     */
    public function testPostDisabled()
    {
        $body     = 'grant_type=client_credentials';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('Disabled:qf2vX10Ec3wFZHx0K1eL'),
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
     * Request via app key and secret
     */
    public function testPostApp()
    {
        $body     = 'grant_type=client_credentials';
        $response = $this->sendRequest('/authorization/token', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Basic ' . base64_encode('5347307d-d801-4075-9aaa-a21a29a448c5:342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $body);

        // we receive only the authorization scope since out user has not the backend scope
        $this->assertAccessToken($response, 'authorization,openid,foo,bar', 2, 3);
    }

    /**
     * A pending app can no request an access token
     */
    public function testPostAppPending()
    {
        $body     = 'grant_type=client_credentials';
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
     * A deactivated app can no request an access token
     */
    public function testPostAppDeactivated()
    {
        $body     = 'grant_type=client_credentials';
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

    private function assertAccessToken(ResponseInterface $response, string $scope, int $userId, ?int $appId = null): void
    {
        $body = (string) $response->getBody();
        $data = Parser::decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);

        $expireDate = strtotime('+2 days');

        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertEquals(172800, $data['expires_in']);
        $this->assertArrayHasKey('scope', $data);
        $this->assertEquals($scope, $data['scope']);

        // check whether the token was created
        $row = $this->connection->fetchAssociative('SELECT app_id, user_id, status, token, scope, expire, date FROM fusio_token WHERE token = :token', ['token' => $data['access_token']]);

        $this->assertEquals($appId, $row['app_id']);
        $this->assertEquals($userId, $row['user_id']);
        $this->assertEquals(Token::STATUS_ACTIVE, $row['status']);
        $this->assertEquals($data['access_token'], $row['token']);
        $this->assertEquals($scope, $row['scope']);
        $this->assertEquals(date('Y-m-d H:i', $expireDate), date('Y-m-d H:i', strtotime($row['expire'])));
        $this->assertEquals(date('Y-m-d H:i'), substr($row['date'], 0, 16));
    }
}
