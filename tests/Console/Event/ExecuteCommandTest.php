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

namespace Fusio\Impl\Tests\Console\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ExecuteCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExecuteCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = Environment::getService('engine_dispatcher');

        // dispatch event
        $dispatcher->dispatch('foo-event', ['foo' => 'bar']);

        $command = Environment::getService('console')->find('event:execute');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertContains('Execution successful', $actual);

        $responses = $this->connection->fetchAll('SELECT triggerId, subscriptionId, status, code, attempts FROM fusio_event_response');

        $this->assertEquals(2, count($responses));
        $this->assertEquals(1, $responses[0]['triggerId']);
        $this->assertEquals(1, $responses[0]['subscriptionId']);
        $this->assertEquals(1, $responses[0]['status']);
        $this->assertEquals(500, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['attempts']);

        $this->assertEquals(1, $responses[1]['triggerId']);
        $this->assertEquals(2, $responses[1]['subscriptionId']);
        $this->assertEquals(1, $responses[1]['status']);
        $this->assertEquals(500, $responses[1]['code']);
        $this->assertEquals(1, $responses[1]['attempts']);
    }
}
