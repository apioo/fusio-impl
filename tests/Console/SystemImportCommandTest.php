<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Tests\Console;

use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * SystemImportCommandTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SystemImportCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('system:import');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/import.json',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Import successful!/', $display, $display);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'New-Connection',
        ]);

        $this->assertEquals(4, $connection['id']);
        $this->assertEquals('Fusio\Impl\Connection\DBAL', $connection['class']);
        $this->assertEquals(177, strlen($connection['config']));

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, propertyName, source, cache FROM fusio_schema WHERE name = :name', [
            'name' => 'New-Schema',
        ]);

        $source = <<<JSON
{
    "id": "http://phpsx.org#",
    "title": "test",
    "type": "object",
    "properties": {
        "title": {
            "type": "string"
        },
        "content": {
            "type": "string"
        },
        "date": {
            "type": "string",
            "format": "date-time"
        }
    }
}
JSON;

        $this->assertEquals(3, $schema['id']);
        $this->assertEquals(null, $schema['propertyName']);
        $this->assertJsonStringEqualsJsonString($source, $schema['source']);
        $this->assertInstanceOf('PSX\Schema\Schema', unserialize($schema['cache']));

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Conditional-Action',
        ]);

        $this->assertEquals(4, $action['id']);
        $this->assertEquals('Fusio\Impl\Action\Condition', $action['class']);
        $this->assertEquals(['condition' => 'rateLimit.getRequestsPerMonth() < 20', 'true' => '3', 'false' => '1'], unserialize($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/bar',
        ]);

        $this->assertEquals(59, $route['id']);
        $this->assertEquals(1, $route['status']);
        $this->assertEquals('GET|POST|PUT|DELETE', $route['methods']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $route['controller']);

        // check methods
        $methods = $this->connection->fetchAll('SELECT routeId, method, version, status, active, public, request, response, action FROM fusio_routes_method WHERE routeId = :routeId', [
            'routeId' => $route['id'],
        ]);

        $this->assertEquals(1, count($methods));
        $this->assertEquals(['routeId' => 59, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'request' => null, 'response' => 3, 'action' => 4], $methods[0]);
    }
}

