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

use Fusio\Adapter\Sql\Connection\SqlAdvanced;
use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Service;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Schema\SchemaInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * DeployCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DeployCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Deploy successful!/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] connection New-Connection/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] schema Request-Schema/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] schema Response-Schema/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] action Test-Action/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] routes \/bar/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] cronjob New-Cron/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] rate New-Rate/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] event New-Event/', $display, $display);

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_connection WHERE name = :name', [
            'name' => 'New-Connection',
        ]);

        $this->assertEquals(4, $connection['id']);
        $this->assertEquals(SqlAdvanced::class, $connection['class']);
        $this->assertNotEmpty($connection['config']);

        // check schema
        $source = <<<'JSON'
{
    "$ref": "Parameters",
    "definitions": {
        "Parameters": {
            "properties": {
                "count": {
                    "type": "integer"
                }
            },
            "type": "object"
        }
    }
}
JSON;

        $this->assertJsonSchema('Parameters', $source);

        $source = <<<'JSON'
{
    "$ref": "Request-Schema",
    "definitions": {
        "Request-Schema": {
            "properties": {
                "content": {
                    "type": "string"
                },
                "date": {
                    "format": "date-time",
                    "type": "string"
                },
                "title": {
                    "type": "string"
                }
            },
            "type": "object"
        }
    }
}
JSON;

        $this->assertJsonSchema('Request-Schema', $source);

        $source = <<<'JSON'
{
    "$ref": "Test",
    "definitions": {
        "Author": {
            "properties": {
                "email": {
                    "type": "string"
                },
                "name": {
                    "type": "string"
                }
            },
            "type": "object"
        },
        "Test": {
            "properties": {
                "author": {
                    "$ref": "Author"
                },
                "content": {
                    "type": "string"
                },
                "date": {
                    "format": "date-time",
                    "type": "string"
                },
                "title": {
                    "type": "string"
                }
            },
            "type": "object"
        }
    }
}
JSON;

        $this->assertJsonSchema('Response-Schema', $source);
        $this->assertJsonSchema('Error-Schema', $source);

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Test-Action',
        ]);

        $this->assertEquals(UtilStaticResponse::class, $action['class']);
        $this->assertEquals(['response' => '{"foo": "bar"}'], Service\Action::unserializeConfig($action['config']));

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/bar',
        ]);

        $this->assertEquals(1, $route['status']);
        $this->assertEquals('ANY', $route['methods']);
        $this->assertEquals(SchemaApiController::class, $route['controller']);

        // check methods
        $methods = $this->connection->fetchAll('SELECT id, route_id, method, version, status, active, public, description, parameters, request, action FROM fusio_routes_method WHERE route_id = :route_id', [
            'route_id' => $route['id'],
        ]);

        $this->assertEquals(1, count($methods));
        $this->assertEquals('GET', $methods[0]['method']);
        $this->assertEquals(1, $methods[0]['version']);
        $this->assertEquals(Resource::STATUS_DEVELOPMENT, $methods[0]['status']);
        $this->assertEquals(1, $methods[0]['active']);
        $this->assertEquals(1, $methods[0]['public']);
        $this->assertEquals('Example bar GET method', $methods[0]['description']);
        $this->assertEquals('Parameters', $methods[0]['parameters']);
        $this->assertEquals('Request-Schema', $methods[0]['request']);
        $this->assertEquals('Test-Action', $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT method_id, code, response FROM fusio_routes_response WHERE method_id = :method_id ORDER BY id ASC', [
            'method_id' => $methods[0]['id'],
        ]);

        $this->assertEquals(2, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals('Response-Schema', $responses[0]['response']);
        $this->assertEquals(500, $responses[1]['code']);
        $this->assertEquals('Error-Schema', $responses[1]['response']);

        // check scopes
        $responses = $this->connection->fetchAll('SELECT name, description, allow, methods FROM fusio_scope_routes INNER JOIN fusio_scope ON fusio_scope.id = fusio_scope_routes.scope_id WHERE route_id = :route_id ORDER BY fusio_scope.id ASC', [
            'route_id' => $route['id'],
        ]);

        $this->assertEquals(2, count($responses));
        $this->assertEquals('foo', $responses[0]['name']);
        $this->assertEquals('Foo access', $responses[0]['description']);
        $this->assertEquals(1, $responses[0]['allow']);
        $this->assertEquals('GET|POST|PUT|PATCH|DELETE', $responses[0]['methods']);
        $this->assertEquals('bar', $responses[1]['name']);
        $this->assertEquals('Bar access', $responses[1]['description']);
        $this->assertEquals(1, $responses[1]['allow']);
        $this->assertEquals('GET|POST|PUT|PATCH|DELETE', $responses[1]['methods']);

        // check cronjobs
        $cronjob = $this->connection->fetchAssoc('SELECT id, name, cron, action FROM fusio_cronjob ORDER BY id DESC');

        $this->assertEquals(2, $cronjob['id']);
        $this->assertEquals('New-Cron', $cronjob['name']);
        $this->assertEquals('15 * * * *', $cronjob['cron']);
        $this->assertEquals('Test-Action', $cronjob['action']);

        // check rate
        $rate = $this->connection->fetchAssoc('SELECT id, priority, name, rate_limit, timespan FROM fusio_rate ORDER BY id DESC');

        $this->assertEquals(5, $rate['id']);
        $this->assertEquals(16, $rate['priority']);
        $this->assertEquals('New-Rate', $rate['name']);
        $this->assertEquals(3600, $rate['rate_limit']);
        $this->assertEquals('PT1H', $rate['timespan']);

        $rateAllocation = $this->connection->fetchAll('SELECT id, route_id, app_id, authenticated, parameters FROM fusio_rate_allocation WHERE rate_id = 5');

        $this->assertEquals(1, count($rateAllocation));
        $this->assertEquals(5, $rateAllocation[0]['id']);
        $this->assertEquals(null, $rateAllocation[0]['route_id']);
        $this->assertEquals(null, $rateAllocation[0]['app_id']);
        $this->assertEquals(true, $rateAllocation[0]['authenticated']);
        $this->assertEquals('foo=bar', $rateAllocation[0]['parameters']);

        // check event
        $event = $this->connection->fetchAssoc('SELECT id, name, description FROM fusio_event WHERE name = :name', ['name' => 'New-Event']);

        $this->assertEquals('New-Event', $event['name']);
        $this->assertEquals('A description of the event', $event['description']);
    }

    public function testCommandActionInclude()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_action_include.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Deploy successful!/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] action Test-Action/', $display, $display);

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Test-Action',
        ]);

        $this->assertEquals('Fusio\Adapter\Util\Action\UtilStaticResponse', $action['class']);

        $config   = Service\Action::unserializeConfig($action['config']);
        $response = json_decode($config['response'], true);

        $this->assertEquals(['foo' => sys_get_temp_dir()], $response);
    }

    public function testCommandConfig()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_config.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Deploy successful!/', $display, $display);

        // check config
        $config = $this->connection->fetchAssoc('SELECT id, value FROM fusio_config WHERE name = :name', [
            'name' => 'mail_register_subject',
        ]);

        $this->assertEquals(13, $config['id']);
        $this->assertEquals('foo bar', $config['value']);
    }

    public function testCommandConfigInvalid()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_config_invalid.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Unknown config parameter foo/', $display, $display);
    }

    public function testCommandProperties()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_properties.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Deploy successful!/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] action Test-Action/', $display, $display);

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'Test-Action',
        ]);

        $this->assertEquals('Fusio\Adapter\Util\Action\UtilStaticResponse', $action['class']);

        $config   = Service\Action::unserializeConfig($action['config']);
        $response = json_decode($config['response'], true);

        $this->assertEquals(['foo' => sys_get_temp_dir()], $response);
    }

    public function testCommandPropertiesUnknownType()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_properties_unknown_type.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Usage of unknown variable type \"foo\", allowed is \(dir, env\)/', $display, $display);
    }

    public function testCommandPropertiesUnknownKey()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_properties_unknown_key.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Usage of unknown variable key \"foo\", allowed is \(cache, src, public, apps, temp\)/', $display, $display);
    }

    public function testCommandRoutesSchemaInclude()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_routes_schema_include.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/- \[CREATED\] schema Schema/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] action s_Console_System_resource_test-action_php/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] routes \/bar/', $display, $display);

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, source FROM fusio_schema WHERE name = :name', [
            'name' => 'Schema',
        ]);

        $source = <<<'JSON'
{
    "$ref": "Test",
    "definitions": {
        "Author": {
            "properties": {
                "email": {
                    "type": "string"
                },
                "name": {
                    "type": "string"
                }
            },
            "type": "object"
        },
        "Test": {
            "properties": {
                "author": {
                    "$ref": "Author"
                },
                "content": {
                    "type": "string"
                },
                "date": {
                    "format": "date-time",
                    "type": "string"
                },
                "title": {
                    "type": "string"
                }
            },
            "type": "object"
        }
    }
}
JSON;

        $this->assertJsonStringEqualsJsonString($source, $schema['source']);

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
        $this->assertEquals('Schema', $methods[0]['request']);
        $this->assertEquals('s_Console_System_resource_test-action_php', $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT method_id, code, response FROM fusio_routes_response WHERE method_id = :method_id', [
            'method_id' => $methods[0]['id'],
        ]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals('Schema', $responses[0]['response']);
    }

    public function testCommandSchema()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_schema.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/- \[CREATED\] schema Ref-Schema/', $display, $display);

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, source FROM fusio_schema WHERE name = :name', [
            'name' => 'Ref-Schema',
        ]);

        $source = <<<'JSON'
{
    "$import": {
        "remote": "schema:///Test-Schema"
    },
    "properties": {
        "content": {
            "$ref": "remote:Test-Schema"
        },
        "title": {
            "$ref": "remote:Test-Schema"
        }
    },
    "type": "object"
}
JSON;

        $this->assertJsonStringEqualsJsonString($source, $schema['source'], $schema['source']);
    }

    public function testCommandSchemaFile()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_schema_file.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/- \[CREATED\] schema Ref-Schema/', $display, $display);

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, source FROM fusio_schema WHERE name = :name', [
            'name' => 'Ref-Schema',
        ]);

        $source = <<<'JSON'
{
    "$ref": "Test",
    "definitions": {
        "Author": {
            "properties": {
                "email": {
                    "type": "string"
                },
                "name": {
                    "type": "string"
                }
            },
            "type": "object"
        },
        "Test": {
            "properties": {
                "author": {
                    "$ref": "Author"
                },
                "content": {
                    "type": "string"
                },
                "date": {
                    "format": "date-time",
                    "type": "string"
                },
                "title": {
                    "type": "string"
                }
            },
            "type": "object"
        }
    }
}
JSON;

        $this->assertJsonStringEqualsJsonString($source, $schema['source'], $schema['source']);
    }

    public function testCommandApp()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_app.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/- \[CREATED\] app Bar-App/', $display, $display);

        // check app
        $app = $this->connection->fetchAssoc('SELECT id, status, name, app_key, app_secret, parameters FROM fusio_app WHERE name = :name', [
            'name' => 'Bar-App',
        ]);

        $this->assertEquals(6, $app['id']);
        $this->assertEquals(1, $app['status']);
        $this->assertEquals('Bar-App', $app['name']);
        $this->assertNotEmpty($app['app_key']);
        $this->assertNotEmpty($app['app_secret']);
        $this->assertEquals('foo=bar', $app['parameters']);
    }

    public function testCommandUser()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_user.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/- \[CREATED\] user Foo-User/', $display, $display);

        // check user
        $user = $this->connection->fetchAssoc('SELECT id, provider, status, remote_id, name, email, password FROM fusio_user WHERE name = :name', [
            'name' => 'Foo-User',
        ]);

        $this->assertEquals(1, $user['provider']);
        $this->assertEquals(1, $user['status']);
        $this->assertEmpty($user['remote_id']);
        $this->assertEquals('Foo-User', $user['name']);
        $this->assertEquals('foo@bar.com', $user['email']);
        $this->assertTrue(password_verify('test1234!', $user['password']));
    }

    public function testCommandScope()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/resource/deploy_scope.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/- \[CREATED\] scope Foo-Scope/', $display, $display);

        // check scope
        $scope = $this->connection->fetchAssoc('SELECT id, name, description FROM fusio_scope WHERE name = :name', [
            'name' => 'Foo-Scope',
        ]);

        $this->assertEquals('Foo-Scope', $scope['name']);
        $this->assertEquals('Foo scope', $scope['description']);
    }

    private function assertJsonSchema($name, $source)
    {
        $schema = $this->connection->fetchAssoc('SELECT id, source FROM fusio_schema WHERE name = :name', [
            'name' => $name,
        ]);

        $this->assertJsonStringEqualsJsonString($source, $schema['source']);
    }
}
