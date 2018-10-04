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

namespace Fusio\Impl\Tests\Loader;

use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Loader\Context;
use Fusio\Impl\Loader\RoutingParser;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Http\Request;
use PSX\Http\RequestInterface;
use PSX\Uri\Uri;

/**
 * RoutingParserTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class RoutingParserTest extends DbTestCase
{
    protected function setUp()
    {
        parent::setUp();

        // add route with NULL priority
        $this->connection->insert('fusio_routes', [
            'status' => 1,
            'priority' => null,
            'methods' => 'ANY',
            'path' => '/test',
            'controller' => SchemaApiController::class
        ]);
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testResolve($method, $path, $controller)
    {
        $request = new Request(new Uri($path), $method);
        $context = new Context();

        $parser  = new RoutingParser($this->connection);
        $request = $parser->resolve($request, $context);

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertEquals($controller, $context->getSource());
    }

    public function resolveProvider()
    {
        $data = [
            ['GET', '/', SchemaApiController::class],
            ['GET', '/foo', SchemaApiController::class],
            ['GET', '/test', SchemaApiController::class],
        ];

        $inserts = NewInstallation::getData();
        $routes  = $inserts['fusio_routes'] ?? [];

        foreach ($routes as $route) {
            $path = $route['path'];
            $path = preg_replace('/\$[a-z_]+<\[0\-9\]\+>/', '1', $path);

            $data[] = ['GET', $path, $route['controller']];
        }

        return $data;
    }

    /**
     * @dataProvider resolveFailProvider
     */
    public function testResolveFailProvider($method, $path)
    {
        $request = new Request(new Uri($path), $method);
        $context = new Context();

        $parser  = new RoutingParser($this->connection);
        $request = $parser->resolve($request, $context);

        $this->assertEmpty($request);
    }

    public function resolveFailProvider()
    {
        return [
            ['GET', '/bar'],
            ['GET', '/baz'],
        ];
    }
}
