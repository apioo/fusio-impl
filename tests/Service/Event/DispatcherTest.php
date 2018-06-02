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

namespace Fusio\Impl\Tests\Service\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * DispatcherTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class DispatcherTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDispatch()
    {
        $this->newDispatcher()->dispatch('foo-event', ['foo' => 'bar']);

        // check database
        $responses = $this->connection->fetchAll('SELECT eventId, status, payload FROM fusio_event_trigger');

        $this->assertEquals(2, count($responses));
        $this->assertEquals(1, $responses[0]['eventId']);
        $this->assertEquals(2, $responses[0]['status']);
        $this->assertEquals('{"foo":"bar"}', $responses[0]['payload']);

        $this->assertEquals(1, $responses[1]['eventId']);
        $this->assertEquals(1, $responses[1]['status']);
        $this->assertEquals('{"foo":"bar"}', $responses[1]['payload']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDispatchInvalid()
    {
        $this->newDispatcher()->dispatch('bar', ['foo' => 'bar']);
    }

    /**
     * @return DispatcherInterface
     */
    private function newDispatcher()
    {
        return Environment::getService('engine_dispatcher');
    }
}
