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

namespace Fusio\Impl\Tests\Service\Event\Sender;

use Fusio\Impl\Webhook\Message;
use Fusio\Impl\Webhook\Sender\Guzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * GuzzleTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GuzzleTest extends TestCase
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
        $code   = $sender->send($dispatcher, $message);

        $this->assertEquals(200, $code);
        $this->assertEquals(1, count($container));
        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('http://google.com', $container[0]['request']->getUri());
        $this->assertEquals(['application/json'], $container[0]['request']->getHeader('Content-Type'));
        $this->assertJsonStringEqualsJsonString('{"foo":"bar"}', (string) $container[0]['request']->getBody());
    }
}
