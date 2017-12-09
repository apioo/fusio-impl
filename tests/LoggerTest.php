<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests;

use Fusio\Impl\Logger;
use PSX\Http\Request;
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
    public function testLog()
    {
        $request = new Request(new Uri('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $logger  = new Logger($this->connection);
        $logId   = $logger->log(1, 1, 1, '127.0.0.1', $request);

        $log = $this->connection->fetchAssoc('SELECT * FROM fusio_log WHERE id = :id', ['id' => $logId]);

        $this->assertEquals(3, $log['id']);
        $this->assertEquals(1, $log['routeId']);
        $this->assertEquals(1, $log['appId']);
        $this->assertEquals(1, $log['userId']);
        $this->assertEquals('127.0.0.1', $log['ip']);
        $this->assertEquals('FooAgent 1.0', $log['userAgent']);
        $this->assertEquals('GET', $log['method']);
        $this->assertEquals('/foo', $log['path']);
        $this->assertEquals('Content-Type: application/json' . "\n" . 'User-Agent: FooAgent 1.0', $log['header']);
        $this->assertEquals('', $log['body']);
    }

    public function testLogLongPath()
    {
        $request = new Request(new Uri('/foo?param=' . str_repeat('a', 1024)), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $logger  = new Logger($this->connection);
        $logId   = $logger->log(1, 1, 1, '127.0.0.1', $request);

        $log = $this->connection->fetchAssoc('SELECT * FROM fusio_log WHERE id = :id', ['id' => $logId]);

        $this->assertEquals(3, $log['id']);
        $this->assertEquals(1, $log['routeId']);
        $this->assertEquals(1, $log['appId']);
        $this->assertEquals(1, $log['userId']);
        $this->assertEquals('127.0.0.1', $log['ip']);
        $this->assertEquals('FooAgent 1.0', $log['userAgent']);
        $this->assertEquals('GET', $log['method']);
        $this->assertEquals('/foo?param=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $log['path']);
        $this->assertEquals('Content-Type: application/json' . "\n" . 'User-Agent: FooAgent 1.0', $log['header']);
        $this->assertEquals('', $log['body']);
    }

    public function testLogPost()
    {
        $body    = new StringStream('foobar');
        $request = new Request(new Uri('/foo'), 'POST', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']], $body);
        $logger  = new Logger($this->connection);
        $logId   = $logger->log(1, 1, 1, '127.0.0.1', $request);

        $log = $this->connection->fetchAssoc('SELECT * FROM fusio_log WHERE id = :id', ['id' => $logId]);

        $this->assertEquals(3, $log['id']);
        $this->assertEquals(1, $log['routeId']);
        $this->assertEquals(1, $log['appId']);
        $this->assertEquals(1, $log['userId']);
        $this->assertEquals('127.0.0.1', $log['ip']);
        $this->assertEquals('FooAgent 1.0', $log['userAgent']);
        $this->assertEquals('POST', $log['method']);
        $this->assertEquals('/foo', $log['path']);
        $this->assertEquals('Content-Type: application/json' . "\n" . 'User-Agent: FooAgent 1.0', $log['header']);
        $this->assertEquals('foobar', $log['body']);
    }

    public function testAppendError()
    {
        $request = new Request(new Uri('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $logger  = new Logger($this->connection);
        $logId   = $logger->log(1, 1, 1, '127.0.0.1', $request);

        $logger = new Logger($this->connection);
        $logger->appendError($logId, new \Exception('foo'));

        $errors = $this->connection->fetchAll('SELECT * FROM fusio_log_error WHERE logId = :id', ['id' => $logId]);

        $this->assertEquals(1, count($errors));
        $this->assertEquals('foo', $errors[0]['message']);
        $this->assertNotEmpty($errors[0]['trace']);
        $this->assertNotEmpty($errors[0]['file']);
        $this->assertNotEmpty($errors[0]['line']);
    }
}
