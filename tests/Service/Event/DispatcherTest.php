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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
        $this->assertEquals(49, $responses[0]['event_id']);
        $this->assertEquals(2, $responses[0]['status']);
        $this->assertEquals('{"foo":"bar"}', $responses[0]['payload']);

        $this->assertEquals(49, $responses[1]['event_id']);
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
