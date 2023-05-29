<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Service\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Service\Connection\Resolver;
use Fusio\Impl\Service\Event\Dispatcher;
use Fusio\Impl\Service\Event\Executor;
use Fusio\Impl\Service\Event\SenderFactory;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Client\Client;
use PSX\Sql\TableManagerInterface;

/**
 * ExecutorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ExecutorTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testExecute()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], \json_encode(['success' => true])),
            new Response(500, ['Content-Type' => 'application/json'], \json_encode(['success' => false])),
        ]);

        $container = [];
        $executor  = $this->newExecutor($mock, $container);

        $this->dispatchEvent('foo-event', ['foo' => 'bar']);

        // execute requests
        $executor->execute();

        // check requests
        $this->assertEquals(2, count($container));
        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('application/json', $container[0]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[0]['request']->getBody());
        $this->assertEquals(200, $container[0]['response']->getStatusCode());

        $this->assertEquals('POST', $container[1]['request']->getMethod());
        $this->assertEquals('application/json', $container[1]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[1]['request']->getBody());
        $this->assertEquals(500, $container[1]['response']->getStatusCode());

        // check database
        $responses = $this->connection->fetchAllAssociative('SELECT trigger_id, subscription_id, status, code, attempts FROM fusio_event_response ORDER BY id ASC');

        $this->assertEquals(3, count($responses));
        $this->assertEquals(1, $responses[0]['trigger_id']);
        $this->assertEquals(1, $responses[0]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[0]['status']);
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['attempts']);

        $this->assertEquals(2, $responses[1]['trigger_id']);
        $this->assertEquals(1, $responses[1]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[1]['status']);
        $this->assertEquals(200, $responses[1]['code']);
        $this->assertEquals(1, $responses[1]['attempts']);

        $this->assertEquals(2, $responses[2]['trigger_id']);
        $this->assertEquals(2, $responses[2]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_PENDING, $responses[2]['status']);
        $this->assertEquals(500, $responses[2]['code']);
        $this->assertEquals(1, $responses[2]['attempts']);
    }

    public function testExecuteAttemptExceed()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], \json_encode(['success' => true])),
            new Response(500, ['Content-Type' => 'application/json'], \json_encode(['success' => false])),
            new Response(500, ['Content-Type' => 'application/json'], \json_encode(['success' => false])),
            new Response(500, ['Content-Type' => 'application/json'], \json_encode(['success' => false])),
            new Response(500, ['Content-Type' => 'application/json'], \json_encode(['success' => false])),
        ]);

        $container = [];
        $executor  = $this->newExecutor($mock, $container);

        $this->dispatchEvent('foo-event', ['foo' => 'bar']);

        // execute requests
        for ($i = 0; $i < 8; $i++) {
            $executor->execute();
        }

        // check requests
        $this->assertEquals(4, count($container));

        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('application/json', $container[0]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[0]['request']->getBody());
        $this->assertEquals(200, $container[0]['response']->getStatusCode());

        $this->assertEquals('POST', $container[1]['request']->getMethod());
        $this->assertEquals('application/json', $container[1]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[1]['request']->getBody());
        $this->assertEquals(500, $container[1]['response']->getStatusCode());

        $this->assertEquals('POST', $container[2]['request']->getMethod());
        $this->assertEquals('application/json', $container[2]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[2]['request']->getBody());
        $this->assertEquals(500, $container[2]['response']->getStatusCode());

        $this->assertEquals('POST', $container[3]['request']->getMethod());
        $this->assertEquals('application/json', $container[3]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[3]['request']->getBody());
        $this->assertEquals(500, $container[3]['response']->getStatusCode());

        // check database
        $responses = $this->connection->fetchAllAssociative('SELECT trigger_id, subscription_id, status, code, attempts FROM fusio_event_response ORDER BY id ASC');

        $this->assertEquals(3, count($responses));
        $this->assertEquals(1, $responses[0]['trigger_id']);
        $this->assertEquals(1, $responses[0]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[0]['status']);
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['attempts']);

        $this->assertEquals(2, $responses[1]['trigger_id']);
        $this->assertEquals(1, $responses[1]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[1]['status']);
        $this->assertEquals(200, $responses[1]['code']);
        $this->assertEquals(1, $responses[1]['attempts']);

        $this->assertEquals(2, $responses[2]['trigger_id']);
        $this->assertEquals(2, $responses[2]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_EXCEEDED, $responses[2]['status']);
        $this->assertEquals(500, $responses[2]['code']);
        $this->assertEquals(3, $responses[2]['attempts']);
    }

    public function testExecuteExceptionExceeded()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], \json_encode(['success' => true])),
            new RequestException("Error Communicating with Server", new Request('GET', '/foo'))
        ]);

        $container = [];
        $executor  = $this->newExecutor($mock, $container);

        $this->dispatchEvent('foo-event', ['foo' => 'bar']);

        // execute requests
        for ($i = 0; $i < 8; $i++) {
            $executor->execute();
        }

        // check requests
        $this->assertEquals(2, count($container));

        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('application/json', $container[0]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[0]['request']->getBody());
        $this->assertEquals(200, $container[0]['response']->getStatusCode());

        $this->assertEquals('POST', $container[1]['request']->getMethod());
        $this->assertEquals('application/json', $container[1]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[1]['request']->getBody());

        // check database
        $responses = $this->connection->fetchAllAssociative('SELECT trigger_id, subscription_id, status, code, attempts FROM fusio_event_response ORDER BY id ASC');

        $this->assertEquals(3, count($responses));
        $this->assertEquals(1, $responses[0]['trigger_id']);
        $this->assertEquals(1, $responses[0]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[0]['status']);
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['attempts']);

        $this->assertEquals(2, $responses[1]['trigger_id']);
        $this->assertEquals(1, $responses[1]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[1]['status']);
        $this->assertEquals(200, $responses[1]['code']);
        $this->assertEquals(1, $responses[1]['attempts']);

        $this->assertEquals(2, $responses[2]['trigger_id']);
        $this->assertEquals(2, $responses[2]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_EXCEEDED, $responses[2]['status']);
        $this->assertEquals(null, $responses[2]['code']);
        $this->assertEquals(3, $responses[2]['attempts']);
    }

    public function testExecuteErrorThenSuccess()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], \json_encode(['success' => true])),
            new Response(500, ['Content-Type' => 'application/json'], \json_encode(['success' => false])),
            new Response(200, ['Content-Type' => 'application/json'], \json_encode(['success' => true])),
        ]);

        $container = [];
        $executor  = $this->newExecutor($mock, $container);

        $this->dispatchEvent('foo-event', ['foo' => 'bar']);

        // execute requests
        for ($i = 0; $i < 8; $i++) {
            $executor->execute();
        }

        // check requests
        $this->assertEquals(3, count($container));

        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('application/json', $container[0]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[0]['request']->getBody());
        $this->assertEquals(200, $container[0]['response']->getStatusCode());

        $this->assertEquals('POST', $container[1]['request']->getMethod());
        $this->assertEquals('application/json', $container[1]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[1]['request']->getBody());
        $this->assertEquals(500, $container[1]['response']->getStatusCode());

        $this->assertEquals('POST', $container[2]['request']->getMethod());
        $this->assertEquals('application/json', $container[2]['request']->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $container[2]['request']->getBody());
        $this->assertEquals(200, $container[2]['response']->getStatusCode());

        // check database
        $responses = $this->connection->fetchAllAssociative('SELECT trigger_id, subscription_id, status, code, attempts FROM fusio_event_response ORDER BY id ASC');

        $this->assertEquals(3, count($responses));
        $this->assertEquals(1, $responses[0]['trigger_id']);
        $this->assertEquals(1, $responses[0]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[0]['status']);
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['attempts']);

        $this->assertEquals(2, $responses[1]['trigger_id']);
        $this->assertEquals(1, $responses[1]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[1]['status']);
        $this->assertEquals(200, $responses[1]['code']);
        $this->assertEquals(1, $responses[1]['attempts']);

        $this->assertEquals(2, $responses[2]['trigger_id']);
        $this->assertEquals(2, $responses[2]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[2]['status']);
        $this->assertEquals(200, $responses[2]['code']);
        $this->assertEquals(2, $responses[2]['attempts']);
    }

    private function newExecutor($mock, array &$container)
    {
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $httpClient = new Client(['handler' => $stack]);

        return new Executor(
            Environment::getService(TableManagerInterface::class)->getTable(Table\Event\Trigger::class),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Event\Subscription::class),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Event\Response::class),
            $httpClient,
            Environment::getService(Resolver::class),
            Environment::getService(SenderFactory::class)
        );
    }

    private function dispatchEvent(string $name, mixed $payload): void
    {
        $dispatcher = new Dispatcher(
            Environment::getService(TableManagerInterface::class)->getTable(Table\Event::class),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Event\Trigger::class)
        );
        $dispatcher->dispatch($name, $payload);
    }
}
