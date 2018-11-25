<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Mail\Sender;

use Fusio\Impl\Service\Event\Message;
use Fusio\Impl\Service\Event\Sender\Guzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

/**
 * GuzzleTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class GuzzleTest extends \PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $sender = new Guzzle();

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

        $sender = new Guzzle();
        $sender->send($dispatcher, $message);

        $this->assertEquals(1, count($container));
        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('http://google.com', $container[0]['request']->getUri());
        $this->assertEquals(['application/json'], $container[0]['request']->getHeader('Content-Type'));
        $this->assertJsonStringEqualsJsonString('{"foo":"bar"}', (string) $container[0]['request']->getBody());
    }
}
