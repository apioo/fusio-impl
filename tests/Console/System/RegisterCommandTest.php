<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Console\System;

use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RegisterCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RegisterCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('system:register');

        $answers = ['y', '/import', '1'];
        $helper  = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream(implode("\n", $answers) . "\n"));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => 'Fusio\Impl\Tests\Adapter\TestAdapter',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Registration successful/', $display, $display);

        // check action class
        $actionId = $this->connection->fetchColumn('SELECT id FROM fusio_action_class WHERE class = :class', [
            'class' => 'Fusio\Impl\Tests\Adapter\Test\VoidAction',
        ]);

        $this->assertEquals(4, $actionId);

        // check connection class
        $connectionId = $this->connection->fetchColumn('SELECT id FROM fusio_connection_class WHERE class = :class', [
            'class' => 'Fusio\Impl\Tests\Adapter\Test\VoidConnection',
        ]);

        $this->assertEquals(3, $connectionId);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'Adapter-Connection',
        ]);

        $this->assertEquals(2, $connection['id']);
        $this->assertEquals('Fusio\Impl\Tests\Adapter\Test\VoidConnection', $connection['class']);
        $this->assertEquals(69, strlen($connection['config']));

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, source, cache FROM fusio_schema WHERE name = :name', [
            'name' => 'Adapter-Schema',
        ]);

        $source = <<<JSON
{
    "id": "http://fusio-project.org",
    "title": "process",
    "type": "object",
    "properties": {
        "logId": {
            "type": "integer"
        },
        "title": {
            "type": "string"
        },
        "content": {
            "type": "string"
        }
    }
}
JSON;

        $this->assertEquals(3, $schema['id']);
        $this->assertJsonStringEqualsJsonString($source, $schema['source']);
        $this->assertInstanceOf('PSX\Schema\Schema', unserialize($schema['cache']));

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Void-Action',
        ]);

        $this->assertEquals(4, $action['id']);
        $this->assertEquals('Fusio\Impl\Tests\Adapter\Test\VoidAction', $action['class']);
        $this->assertEquals(['foo' => 'bar', 'connection' => '2'], unserialize($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/import/void',
        ]);

        $this->assertEquals(62, $route['id']);
        $this->assertEquals(1, $route['status']);
        $this->assertEquals('GET|POST|PUT|DELETE', $route['methods']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $route['controller']);

        // check methods
        $methods = $this->connection->fetchAll('SELECT routeId, method, version, status, active, public, request, response, action FROM fusio_routes_method WHERE routeId = :routeId', [
            'routeId' => $route['id'],
        ]);

        $this->assertEquals(1, count($methods));
        $this->assertEquals(['routeId' => 62, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'request' => 3, 'response' => 1, 'action' => 4], $methods[0]);
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
