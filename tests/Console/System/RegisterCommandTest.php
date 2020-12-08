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

use Fusio\Adapter;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Provider\ProviderConfig;
use Fusio\Impl\Service;
use Fusio\Impl\Tests\Adapter\Test\VoidAction;
use Fusio\Impl\Tests\Adapter\Test\VoidConnection;
use Fusio\Impl\Tests\Adapter\TestAdapter;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Schema\SchemaInterface;
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
        $answers = ['y', '1'];

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($answers);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => TestAdapter::class,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Registration successful/', $display, $display);

        // check action class
        $file   = Environment::getService('config')->get('fusio_provider');
        $config = ProviderConfig::fromFile($file);

        $actual = array_values($config->get(ProviderConfig::TYPE_ACTION));
        $expect = [
            Adapter\File\Action\FileProcessor::class,
            Adapter\GraphQL\Action\GraphQLProcessor::class,
            Adapter\Http\Action\HttpProcessor::class,
            Adapter\Php\Action\PhpProcessor::class,
            Adapter\Php\Action\PhpSandbox::class,
            Adapter\Smtp\Action\SmtpSend::class,
            Adapter\Sql\Action\SqlSelectAll::class,
            Adapter\Sql\Action\SqlSelectRow::class,
            Adapter\Sql\Action\SqlInsert::class,
            Adapter\Sql\Action\SqlUpdate::class,
            Adapter\Sql\Action\SqlDelete::class,
            Adapter\Sql\Action\Query\SqlQueryAll::class,
            Adapter\Sql\Action\Query\SqlQueryRow::class,
            Adapter\Util\Action\UtilStaticResponse::class,
            VoidAction::class,
        ];

        $this->assertEquals($expect, $actual);

        // check connection class
        $actual = array_values($config->get(ProviderConfig::TYPE_CONNECTION));
        $expect = [
            Adapter\File\Connection\Ftp::class,
            Adapter\GraphQL\Connection\GraphQL::class,
            Adapter\Http\Connection\Http::class,
            Adapter\Smtp\Connection\Smtp::class,
            Adapter\Soap\Connection\Soap::class,
            Adapter\Sql\Connection\Sql::class,
            Adapter\Sql\Connection\SqlAdvanced::class,
            VoidConnection::class,
        ];

        $this->assertEquals($expect, $actual);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'Adapter-Connection',
        ]);

        $this->assertEquals(4, $connection['id']);
        $this->assertEquals(VoidConnection::class, $connection['class']);
        $this->assertNotEmpty($connection['config']);

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, source FROM fusio_schema WHERE name = :name', [
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

        $this->assertEquals(139, $schema['id']);
        $this->assertJsonStringEqualsJsonString($source, $schema['source']);

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, engine, config FROM fusio_action WHERE name = :name', [
            'name' => 'Void-Action',
        ]);

        $this->assertEquals(VoidAction::class, $action['class']);
        $this->assertEquals(PhpClass::class, $action['engine']);
        $this->assertEquals(['foo' => 'bar', 'connection' => 'Adapter-Connection'], Service\Action::unserializeConfig($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/void',
        ]);

        $this->assertEquals(1, $route['status']);
        $this->assertEquals('ANY', $route['methods']);
        $this->assertEquals(SchemaApiController::class, $route['controller']);

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
        $this->assertEquals('Adapter-Schema', $methods[0]['request']);
        $this->assertEquals('Void-Action', $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT method_id, code, response FROM fusio_routes_response WHERE method_id = :method_id', [
            'method_id' => $methods[0]['id'],
        ]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals('Passthru', $responses[0]['response']);
    }

    public function testCommandAutoConfirm()
    {
        $command = Environment::getService('console')->find('system:register');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => TestAdapter::class,
            '--yes'   => true,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Registration successful/', $display, $display);

        // check action class
        $file   = Environment::getService('config')->get('fusio_provider');
        $config = ProviderConfig::fromFile($file);

        $actual = array_values($config->get(ProviderConfig::TYPE_ACTION));
        $expect = [
            Adapter\File\Action\FileProcessor::class,
            Adapter\GraphQL\Action\GraphQLProcessor::class,
            Adapter\Http\Action\HttpProcessor::class,
            Adapter\Php\Action\PhpProcessor::class,
            Adapter\Php\Action\PhpSandbox::class,
            Adapter\Smtp\Action\SmtpSend::class,
            Adapter\Sql\Action\SqlSelectAll::class,
            Adapter\Sql\Action\SqlSelectRow::class,
            Adapter\Sql\Action\SqlInsert::class,
            Adapter\Sql\Action\SqlUpdate::class,
            Adapter\Sql\Action\SqlDelete::class,
            Adapter\Sql\Action\Query\SqlQueryAll::class,
            Adapter\Sql\Action\Query\SqlQueryRow::class,
            Adapter\Util\Action\UtilStaticResponse::class,
            VoidAction::class,
        ];

        $this->assertEquals($expect, $actual);

        // check connection class
        $actual = array_values($config->get(ProviderConfig::TYPE_CONNECTION));
        $expect = [
            Adapter\File\Connection\Ftp::class,
            Adapter\GraphQL\Connection\GraphQL::class,
            Adapter\Http\Connection\Http::class,
            Adapter\Smtp\Connection\Smtp::class,
            Adapter\Soap\Connection\Soap::class,
            Adapter\Sql\Connection\Sql::class,
            Adapter\Sql\Connection\SqlAdvanced::class,
            VoidConnection::class,
        ];

        $this->assertEquals($expect, $actual);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'Adapter-Connection',
        ]);

        $this->assertEquals(4, $connection['id']);
        $this->assertEquals(VoidConnection::class, $connection['class']);
        $this->assertNotEmpty($connection['config']);

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, source FROM fusio_schema WHERE name = :name', [
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

        $this->assertEquals(139, $schema['id']);
        $this->assertJsonStringEqualsJsonString($source, $schema['source']);

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Void-Action',
        ]);

        $this->assertEquals(VoidAction::class, $action['class']);
        $this->assertEquals(['foo' => 'bar', 'connection' => 'Adapter-Connection'], Service\Action::unserializeConfig($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/void',
        ]);

        $this->assertEquals(1, $route['status']);
        $this->assertEquals('ANY', $route['methods']);
        $this->assertEquals(SchemaApiController::class, $route['controller']);

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
        $this->assertEquals('Adapter-Schema', $methods[0]['request']);
        $this->assertEquals('Void-Action', $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT method_id, code, response FROM fusio_routes_response WHERE method_id = :method_id', [
            'method_id' => $methods[0]['id'],
        ]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals('Passthru', $responses[0]['response']);
    }
}
