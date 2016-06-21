<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Tests\Consumer;

use Firebase\JWT\JWT;
use Fusio\Impl\Service\Consumer\ProviderInterface;
use Fusio\Impl\Table\User;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Handler\Mock;
use PSX\Http\RequestInterface;

/**
 * ProviderTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPostFacebook()
    {
        $handler = new Mock();
        $handler->add('GET', 'https://graph.facebook.com/v2.5/me?access_token=e72e16c7e42f292c6912e7710c838347ae178b4a&fields=id%2Cemail%2Cfirst_name%2Clast_name%2Clink%2Cname', function(RequestInterface $request){
            $this->assertEquals('Bearer e72e16c7e42f292c6912e7710c838347ae178b4a', $request->getHeader('Authorization'));

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['id' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com']);

            return $response;
        });

        $handler->add('GET', 'https://graph.facebook.com/v2.5/oauth/access_token?client_id=bar&redirect_uri=' . urlencode('http://google.com') . '&client_secret=facebook&code=foo', function(RequestInterface $request){
            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'token_type' => 'bearer', 'expires_in' => time() + 60]);

            return $response;
        });

        Environment::getService('http_client')->setHandler($handler);
        Environment::getService('config')->set('fusio_login_provider', ['facebook' => ['secret' => 'facebook']]);

        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/facebook', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'code' => 'foo',
            'clientId' => 'bar',
            'redirectUri' => 'http://google.com',
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);
        $token = JWT::decode($data->token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals(6, $token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertNotEmpty($token->jti);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('appId', 'userId', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(1, $row['appId']);
        $this->assertEquals(6, $row['userId']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals($row['token'], $token->jti);
        $this->assertEquals(Environment::getService('config')->get('fusio_scopes_default'), $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);

        // check new user
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('status', 'provider', 'remoteId', 'name', 'email', 'password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $row['userId']]);

        $this->assertEquals(User::STATUS_CONSUMER, $row['status']);
        $this->assertEquals(ProviderInterface::PROVIDER_FACEBOOK, $row['provider']);
        $this->assertEquals('1', $row['remoteId']);
        $this->assertEquals('octocat', $row['name']);
        $this->assertEquals('octocat@github.com', $row['email']);
        $this->assertEquals(null, $row['password']);
    }

    public function testPostGithub()
    {
        $handler = new Mock();
        $handler->add('GET', 'https://api.github.com/user', function(RequestInterface $request){
            $this->assertEquals('Bearer e72e16c7e42f292c6912e7710c838347ae178b4a', $request->getHeader('Authorization'));

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['id' => 1, 'login' => 'octocat', 'email' => 'octocat@github.com']);

            return $response;
        });

        $handler->add('POST', 'https://github.com/login/oauth/access_token', function(RequestInterface $request){
            $body = (string) $request->getBody();
            $this->assertEquals('code=foo&client_id=bar&client_secret=github&redirect_uri=http%3A%2F%2Fgoogle.com', $body);

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'scope' => 'user,gist', 'token_type' => 'bearer']);

            return $response;
        });

        Environment::getService('http_client')->setHandler($handler);
        Environment::getService('config')->set('fusio_login_provider', ['github' => ['secret' => 'github']]);

        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'code' => 'foo',
            'clientId' => 'bar',
            'redirectUri' => 'http://google.com',
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);
        $token = JWT::decode($data->token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals(6, $token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertNotEmpty($token->jti);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('appId', 'userId', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(1, $row['appId']);
        $this->assertEquals(6, $row['userId']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals($row['token'], $token->jti);
        $this->assertEquals(Environment::getService('config')->get('fusio_scopes_default'), $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);

        // check new user
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('status', 'provider', 'remoteId', 'name', 'email', 'password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $row['userId']]);

        $this->assertEquals(User::STATUS_CONSUMER, $row['status']);
        $this->assertEquals(ProviderInterface::PROVIDER_GITHUB, $row['provider']);
        $this->assertEquals('1', $row['remoteId']);
        $this->assertEquals('octocat', $row['name']);
        $this->assertEquals('octocat@github.com', $row['email']);
        $this->assertEquals(null, $row['password']);
    }

    public function testPostGoogle()
    {
        $handler = new Mock();
        $handler->add('GET', 'https://www.googleapis.com/plus/v1/people/me/openIdConnect', function(RequestInterface $request){
            $this->assertEquals('Bearer e72e16c7e42f292c6912e7710c838347ae178b4a', $request->getHeader('Authorization'));

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['sub' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com']);

            return $response;
        });

        $handler->add('POST', 'https://accounts.google.com/o/oauth2/token', function(RequestInterface $request){
            $body = (string) $request->getBody();
            $this->assertEquals('code=foo&client_id=bar&client_secret=google&redirect_uri=http%3A%2F%2Fgoogle.com&grant_type=authorization_code', $body);

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a']);

            return $response;
        });

        Environment::getService('http_client')->setHandler($handler);
        Environment::getService('config')->set('fusio_login_provider', ['google' => ['secret' => 'google']]);

        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/google', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'code' => 'foo',
            'clientId' => 'bar',
            'redirectUri' => 'http://google.com',
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);
        $token = JWT::decode($data->token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals(6, $token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertNotEmpty($token->jti);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('appId', 'userId', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(1, $row['appId']);
        $this->assertEquals(6, $row['userId']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals($row['token'], $token->jti);
        $this->assertEquals(Environment::getService('config')->get('fusio_scopes_default'), $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);

        // check new user
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('status', 'provider', 'remoteId', 'name', 'email', 'password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $row['userId']]);

        $this->assertEquals(User::STATUS_CONSUMER, $row['status']);
        $this->assertEquals(ProviderInterface::PROVIDER_GOOGLE, $row['provider']);
        $this->assertEquals('1', $row['remoteId']);
        $this->assertEquals('octocat', $row['name']);
        $this->assertEquals('octocat@github.com', $row['email']);
        $this->assertEquals(null, $row['password']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
