<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Service;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Schema\SchemaInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ImportCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ImportCommandTest extends ControllerDbTestCase
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
            'file'    => __DIR__ . '/resource/import.json',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Import successful!/', $display, $display);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'New-Connection',
        ]);

        $this->assertEquals(4, $connection['id']);
        $this->assertEquals('Fusio\Adapter\Sql\Connection\Sql', $connection['class']);
        $this->assertNotEmpty($connection['config']);

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, source FROM fusio_schema WHERE name = :name', [
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

        $this->assertEquals(142, $schema['id']);
        $this->assertJsonStringEqualsJsonString($source, $schema['source']);

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Test-Action',
        ]);

        $this->assertEquals('Fusio\Adapter\Util\Action\UtilStaticResponse', $action['class']);
        $this->assertEquals(['response' => '{"foo": "bar"}'], Service\Action::unserializeConfig($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/bar',
        ]);

        $this->assertEquals(1, $route['status']);
        $this->assertEquals('ANY', $route['methods']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $route['controller']);

        // check methods
        $methods = $this->connection->fetchAll('SELECT id, route_id, method, version, status, active, public, parameters, request, action FROM fusio_routes_method WHERE route_id = :route_id', [
            'route_id' => $route['id'],
        ]);

        $this->assertEquals(1, count($methods));
        $this->assertEquals('GET', $methods[0]['method']);
        $this->assertEquals(1, $methods[0]['version']);
        $this->assertEquals(Resource::STATUS_DEVELOPMENT, $methods[0]['status']);
        $this->assertEquals(1, $methods[0]['active']);
        $this->assertEquals(1, $methods[0]['public']);
        $this->assertEquals(null, $methods[0]['parameters']);
        $this->assertEquals(null, $methods[0]['request']);
        $this->assertEquals('Test-Action', $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT method_id, code, response FROM fusio_routes_response WHERE method_id = :method_id', [
            'method_id' => $methods[0]['id'],
        ]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals('New-Schema', $responses[0]['response']);

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/baz',
        ]);

        $this->assertEquals(1, $route['status']);
        $this->assertEquals('ANY', $route['methods']);
        $this->assertEquals(SchemaApiController::class, $route['controller']);

        // check methods
        $methods = $this->connection->fetchAll('SELECT id, route_id, method, version, status, active, public, parameters, request, action FROM fusio_routes_method WHERE route_id = :route_id', [
            'route_id' => $route['id'],
        ]);

        $this->assertEquals(1, count($methods));
        $this->assertEquals('POST', $methods[0]['method']);
        $this->assertEquals(1, $methods[0]['version']);
        $this->assertEquals(Resource::STATUS_DEVELOPMENT, $methods[0]['status']);
        $this->assertEquals(1, $methods[0]['active']);
        $this->assertEquals(1, $methods[0]['public']);
        $this->assertEquals('New-Schema', $methods[0]['parameters']);
        $this->assertEquals('New-Schema', $methods[0]['request']);
        $this->assertEquals('Test-Action', $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT method_id, code, response FROM fusio_routes_response WHERE method_id = :method_id ORDER BY id ASC', [
            'method_id' => $methods[0]['id'],
        ]);

        $this->assertEquals(2, count($responses));
        $this->assertEquals(201, $responses[0]['code']);
        $this->assertEquals('New-Schema', $responses[0]['response']);
        $this->assertEquals(500, $responses[1]['code']);
        $this->assertEquals('Error-Schema', $responses[1]['response']);
    }

    public function testCommandOpenAPI()
    {
        $command = Environment::getService('console')->find('system:import');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/openapi.json',
            'format'  => 'openapi'
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Import successful!/', $display, $display);

        // check schema
        $actual = $this->fetchList('SELECT name FROM fusio_schema');

        $this->assertContains('Error', $actual);
        $this->assertContains('Pet', $actual);
        $this->assertContains('Pets', $actual);
        $this->assertContains('PetsGetQuery', $actual);
        $this->assertContains('PetsPetIdGetQuery', $actual);

        // check action
        $actual = $this->fetchList('SELECT name FROM fusio_action');

        $this->assertContains('pets-_petId-showPetById-GET', $actual);
        $this->assertContains('pets-createPets-POST', $actual);
        $this->assertContains('pets-listPets-GET', $actual);

        // check routes
        $actual = $this->fetchList('SELECT path FROM fusio_routes');

        $this->assertContains('/pets', $actual);
        $this->assertContains('/pets/:petId', $actual);
    }

    private function fetchList($sql)
    {
        $result = $this->connection->fetchAll($sql);
        $result = array_map(function($value){ 
            return reset($value); 
        }, $result);
        
        return $result;
    }
}
