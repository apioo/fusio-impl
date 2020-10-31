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

namespace Fusio\Impl\Tests\Console\System\Deploy;

use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Factory\Resolver\HttpUrl;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * DeployCommandRouteHttpsTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DeployCommandRouteHttpsTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommandRoutesActionClass()
    {
        $command = Environment::getService('console')->find('system:deploy');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file'    => __DIR__ . '/../resource/deploy_routes_action_https.yaml',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/- \[CREATED\] action httpbin_org_get/', $display, $display);
        $this->assertRegExp('/- \[CREATED\] routes \/bar/', $display, $display);

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, class, engine, config FROM fusio_action WHERE name = :name', [
            'name' => 'httpbin_org_get',
        ]);

        $this->assertEquals(167, $action['id']);
        $this->assertEquals('https://httpbin.org/get', $action['class']);
        $this->assertEquals(HttpUrl::class, $action['engine']);
        $this->assertEquals(null, $action['config']);

        // check routes
        $route = $this->connection->fetchAssoc('SELECT id, status, methods, controller FROM fusio_routes WHERE path = :path', [
            'path' => '/bar',
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
        $this->assertEquals(null, $methods[0]['request']);
        $this->assertEquals('httpbin_org_get', $methods[0]['action']);

        // check responses
        $responses = $this->connection->fetchAll('SELECT method_id, code, response FROM fusio_routes_response WHERE method_id = :method_id', [
            'method_id' => $methods[0]['id'],
        ]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals('Passthru', $responses[0]['response']);
    }
}
