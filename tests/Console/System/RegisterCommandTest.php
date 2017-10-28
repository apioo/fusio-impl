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

namespace Fusio\Impl\Tests\Console\System;

use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Tests\Adapter\Test\VoidAction;
use Fusio\Impl\Tests\Adapter\Test\VoidConnection;
use Fusio\Impl\Tests\Adapter\TestAdapter;
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
            'class'   => TestAdapter::class,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Registration successful/', $display, $display);

        // check action class
        $actionId = $this->connection->fetchColumn('SELECT id FROM fusio_action_class WHERE class = :class', [
            'class' => VoidAction::class,
        ]);

        $this->assertEquals(7, $actionId);

        // check connection class
        $connectionId = $this->connection->fetchColumn('SELECT id FROM fusio_connection_class WHERE class = :class', [
            'class' => VoidConnection::class,
        ]);

        $this->assertEquals(4, $connectionId);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'Adapter-Connection',
        ]);

        $this->assertEquals(2, $connection['id']);
        $this->assertEquals(VoidConnection::class, $connection['class']);
        $this->assertNotEmpty($connection['config']);

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

        $this->assertEquals(4, $schema['id']);
        $this->assertJsonStringEqualsJsonString($source, $schema['source']);
        $this->assertInstanceOf('PSX\Schema\Schema', unserialize($schema['cache']));

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, engine, config FROM fusio_action WHERE name = :name', [
            'name' => 'Void-Action',
        ]);

        $this->assertEquals(5, $action['id']);
        $this->assertEquals(VoidAction::class, $action['class']);
        $this->assertEquals(PhpClass::class, $action['engine']);
        $this->assertEquals(['foo' => 'bar', 'connection' => '2'], unserialize($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/import/void',
        ]);

        $this->assertEquals(Fixture::getLastRouteId() + 3, $route['id']);
        $this->assertEquals(1, $route['status']);
        $this->assertEquals('ANY', $route['methods']);
        $this->assertEquals(SchemaApiController::class, $route['controller']);

        // check methods
        $methods = $this->connection->fetchAll('SELECT id, routeId, method, version, status, active, public, parameters, request, action FROM fusio_routes_method WHERE routeId = :routeId', [
            'routeId' => $route['id'],
        ]);

        $this->assertEquals(1, count($methods));
        $this->assertEquals(Fixture::getLastRouteId() + 3, $methods[0]['routeId']);
        $this->assertEquals('GET', $methods[0]['method']);
        $this->assertEquals(1, $methods[0]['version']);
        $this->assertEquals(Resource::STATUS_DEVELOPMENT, $methods[0]['status']);
        $this->assertEquals(1, $methods[0]['active']);
        $this->assertEquals(1, $methods[0]['public']);
        $this->assertEquals(null, $methods[0]['parameters']);
        $this->assertEquals(4, $methods[0]['request']);
        $this->assertEquals(5, $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT methodId, code, response FROM fusio_routes_response WHERE methodId = :methodId', [
            'methodId' => $methods[0]['id'],
        ]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['response']);
    }

    public function testCommandAutoConfirm()
    {
        $command = Environment::getService('console')->find('system:register');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => TestAdapter::class,
            'path'    => '/import',
            '--yes'   => true,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Registration successful/', $display, $display);

        // check action class
        $actionId = $this->connection->fetchColumn('SELECT id FROM fusio_action_class WHERE class = :class', [
            'class' => VoidAction::class,
        ]);

        $this->assertEquals(7, $actionId);

        // check connection class
        $connectionId = $this->connection->fetchColumn('SELECT id FROM fusio_connection_class WHERE class = :class', [
            'class' => VoidConnection::class,
        ]);

        $this->assertEquals(4, $connectionId);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'Adapter-Connection',
        ]);

        $this->assertEquals(2, $connection['id']);
        $this->assertEquals(VoidConnection::class, $connection['class']);
        $this->assertNotEmpty($connection['config']);

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

        $this->assertEquals(4, $schema['id']);
        $this->assertJsonStringEqualsJsonString($source, $schema['source']);
        $this->assertInstanceOf('PSX\Schema\Schema', unserialize($schema['cache']));

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Void-Action',
        ]);

        $this->assertEquals(5, $action['id']);
        $this->assertEquals(VoidAction::class, $action['class']);
        $this->assertEquals(['foo' => 'bar', 'connection' => '2'], unserialize($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/import/void',
        ]);

        $this->assertEquals(Fixture::getLastRouteId() + 3, $route['id']);
        $this->assertEquals(1, $route['status']);
        $this->assertEquals('ANY', $route['methods']);
        $this->assertEquals(SchemaApiController::class, $route['controller']);

        // check methods
        $methods = $this->connection->fetchAll('SELECT id, routeId, method, version, status, active, public, parameters, request, action FROM fusio_routes_method WHERE routeId = :routeId', [
            'routeId' => $route['id'],
        ]);

        $this->assertEquals(1, count($methods));
        $this->assertEquals(Fixture::getLastRouteId() + 3, $methods[0]['routeId']);
        $this->assertEquals('GET', $methods[0]['method']);
        $this->assertEquals(1, $methods[0]['version']);
        $this->assertEquals(Resource::STATUS_DEVELOPMENT, $methods[0]['status']);
        $this->assertEquals(1, $methods[0]['active']);
        $this->assertEquals(1, $methods[0]['public']);
        $this->assertEquals(null, $methods[0]['parameters']);
        $this->assertEquals(4, $methods[0]['request']);
        $this->assertEquals(5, $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT methodId, code, response FROM fusio_routes_response WHERE methodId = :methodId', [
            'methodId' => $methods[0]['id'],
        ]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['response']);
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
