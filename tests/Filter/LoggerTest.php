<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Filter;

use Fusio\Engine\Model\App;
use Fusio\Engine\Model\User;
use Fusio\Impl\Filter\Logger;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Filter\FilterChain;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\Stream\StringStream;
use PSX\Uri\Uri;

/**
 * LoggerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class LoggerTest extends DbTestCase
{
    public function testHandle()
    {
        $request  = new Request(new Uri('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->setMethods(['handle'])
            ->getMock();

        $filterChain->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $logService = Environment::getService('log_service');

        $logger = new Logger($logService, $this->newContext());
        $logger->handle($request, $response, $filterChain);

        $log = $this->connection->fetchAssoc('SELECT * FROM fusio_log WHERE id = :id', ['id' => 3]);

        $this->assertEquals(3, $log['id']);
        $this->assertEquals(1, $log['route_id']);
        $this->assertEquals(1, $log['app_id']);
        $this->assertEquals(1, $log['user_id']);
        $this->assertEquals('127.0.0.1', $log['ip']);
        $this->assertEquals('FooAgent 1.0', $log['user_agent']);
        $this->assertEquals('GET', $log['method']);
        $this->assertEquals('/foo', $log['path']);
        $this->assertEquals('Content-Type: application/json' . "\n" . 'User-Agent: FooAgent 1.0', $log['header']);
        $this->assertEquals('', $log['body']);
    }

    public function testHandleLongPath()
    {
        $request  = new Request(new Uri('/foo?param=' . str_repeat('a', 1024)), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->setMethods(['handle'])
            ->getMock();

        $filterChain->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $logService = Environment::getService('log_service');

        $logger = new Logger($logService, $this->newContext());
        $logger->handle($request, $response, $filterChain);

        $log = $this->connection->fetchAssoc('SELECT * FROM fusio_log WHERE id = :id', ['id' => 3]);

        $this->assertEquals(3, $log['id']);
        $this->assertEquals(1, $log['route_id']);
        $this->assertEquals(1, $log['app_id']);
        $this->assertEquals(1, $log['user_id']);
        $this->assertEquals('127.0.0.1', $log['ip']);
        $this->assertEquals('FooAgent 1.0', $log['user_agent']);
        $this->assertEquals('GET', $log['method']);
        $this->assertEquals('/foo?param=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $log['path']);
        $this->assertEquals('Content-Type: application/json' . "\n" . 'User-Agent: FooAgent 1.0', $log['header']);
        $this->assertEquals('', $log['body']);
    }

    public function testHandlePost()
    {
        $body     = new StringStream('foobar');
        $request  = new Request(new Uri('/foo'), 'POST', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']], $body);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->setMethods(['handle'])
            ->getMock();

        $filterChain->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $logService = Environment::getService('log_service');

        $logger = new Logger($logService, $this->newContext());
        $logger->handle($request, $response, $filterChain);

        $log = $this->connection->fetchAssoc('SELECT * FROM fusio_log WHERE id = :id', ['id' => 3]);

        $this->assertEquals(3, $log['id']);
        $this->assertEquals(1, $log['route_id']);
        $this->assertEquals(1, $log['app_id']);
        $this->assertEquals(1, $log['user_id']);
        $this->assertEquals('127.0.0.1', $log['ip']);
        $this->assertEquals('FooAgent 1.0', $log['user_agent']);
        $this->assertEquals('POST', $log['method']);
        $this->assertEquals('/foo', $log['path']);
        $this->assertEquals('Content-Type: application/json' . "\n" . 'User-Agent: FooAgent 1.0', $log['header']);
        $this->assertEquals('foobar', $log['body']);
    }

    public function testAppendError()
    {
        $request  = new Request(new Uri('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->setMethods(['handle'])
            ->getMock();

        $filterChain->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response))
            ->willReturnCallback(function(){
                throw new \RuntimeException('foo');
            });

        try {
            $logService = Environment::getService('log_service');

            $logger = new Logger($logService, $this->newContext());
            $logger->handle($request, $response, $filterChain);
            
            $this->fail('Should throw an exception');
        } catch (\RuntimeException $e) {
        }

        $error = $this->connection->fetchAssoc('SELECT * FROM fusio_log_error WHERE id = :id', ['id' => 2]);

        $this->assertEquals(2, $error['id']);
        $this->assertEquals(3, $error['log_id']);
        $this->assertEquals('foo', $error['message']);
    }

    public function testAppendErrorLongMessage()
    {
        $request  = new Request(new Uri('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->setMethods(['handle'])
            ->getMock();

        $filterChain->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response))
            ->willReturnCallback(function(){
                throw new \RuntimeException(str_repeat('a', 600));
            });

        try {
            $logService = Environment::getService('log_service');

            $logger = new Logger($logService, $this->newContext());
            $logger->handle($request, $response, $filterChain);

            $this->fail('Should throw an exception');
        } catch (\RuntimeException $e) {
        }

        $error = $this->connection->fetchAssoc('SELECT * FROM fusio_log_error WHERE id = :id', ['id' => 2]);

        $this->assertEquals(2, $error['id']);
        $this->assertEquals(3, $error['log_id']);
        $this->assertEquals(str_repeat('a', 500), $error['message']);
    }

    private function newContext()
    {
        $app = new App();
        $app->setId(1);

        $user = new User();
        $user->setId(1);

        $context = new Context();
        $context->setRouteId(1);
        $context->setApp($app);
        $context->setUser($user);

        return $context;
    }
}
