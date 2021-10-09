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

namespace Fusio\Impl\Tests\Service\Event\Sender;

use Fusio\Impl\Service\Event\Message;
use Fusio\Impl\Service\Event\Sender\HTTP;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PSX\Http\Client\Client;

/**
 * HTTPTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class HTTPTest extends TestCase
{
    public function testAccept()
    {
        $sender = new HTTP();

        $this->assertTrue($sender->accept(new Client()));
        $this->assertFalse($sender->accept(new \stdClass()));
    }

    public function testSend()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);

        $container = [];
        $history   = Middleware::history($container);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $dispatcher = new Client(['handler' => $handler]);

        $message = new Message('http://google.com', \json_encode(['foo' => 'bar']));

        $sender = new HTTP();
        $code   = $sender->send($dispatcher, $message);

        $this->assertEquals(200, $code);
        $this->assertEquals(1, count($container));
        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('http://google.com', $container[0]['request']->getUri());
        $this->assertEquals(['application/json'], $container[0]['request']->getHeader('Content-Type'));
        $this->assertJsonStringEqualsJsonString('{"foo":"bar"}', (string) $container[0]['request']->getBody());
    }
}
