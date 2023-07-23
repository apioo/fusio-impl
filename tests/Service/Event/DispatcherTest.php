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

namespace Fusio\Impl\Tests\Service\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Service\Event\Dispatcher;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Sql\TableManagerInterface;

/**
 * DispatcherTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class DispatcherTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testDispatch()
    {
        $this->newDispatcher()->dispatch('foo-event', ['foo' => 'bar']);

        // check database
        $responses = $this->connection->fetchAllAssociative('SELECT event_id, status, payload FROM fusio_event_trigger');

        // payload from fixture
        $this->assertEquals(2, count($responses));
        $this->assertEquals(56, $responses[0]['event_id']);
        $this->assertEquals(2, $responses[0]['status']);
        $this->assertEquals('{"foo":"bar"}', $responses[0]['payload']);

        $this->assertEquals(56, $responses[1]['event_id']);
        $this->assertEquals(1, $responses[1]['status']);
        $this->assertEquals('{"foo":"bar"}', $responses[1]['payload']);
    }

    public function testDispatchInvalid()
    {
        $this->expectException(\RuntimeException::class);

        $this->newDispatcher()->dispatch('bar', ['foo' => 'bar']);
    }

    private function newDispatcher(): DispatcherInterface
    {
        return new Dispatcher(
            Environment::getService(TableManagerInterface::class)->getTable(Table\Event::class),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Event\Trigger::class)
        );
    }
}
