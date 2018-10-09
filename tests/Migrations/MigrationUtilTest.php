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

namespace Fusio\Impl\Tests\Migration;

use Fusio\Impl\Backend;
use Fusio\Impl\Migrations\MigrationUtil;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * MigrationUtilTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class MigrationUtilTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testSyncRoute()
    {
        $data   = NewInstallation::getData();
        $routes = $data['fusio_routes'];

        // update route
        $routes[1]['priority'] = 268435476;
        $routes[1]['controller'] = \stdClass::class;

        // delete route
        unset($routes[2]);

        // add route
        $routes[] = ['status' => 1, 'priority' => 0x10000000 | 80, 'methods' => 'ANY', 'path' => '/backend/foo', 'controller' => \stdClass::class];

        // expected queries
        $queries = [
            [
                'UPDATE fusio_routes SET priority = :priority, controller = :controller WHERE id = :id',
                ['priority' => 268435476, 'controller' => \stdClass::class, 'id' => 2],
            ],
            [
                'INSERT INTO fusio_routes (status, priority, methods, path, controller) VALUES (?, ?, ?, ?, ?)',
                [1, 268435536, 'ANY', '/backend/foo', \stdClass::class],
            ],
            [
                'INSERT INTO fusio_scope_routes (scope_id, route_id, allow, methods) VALUES (?, ?, ?, ?)',
                [1, 93, 1, 'GET|POST|PUT|PATCH|DELETE'],
            ]
        ];

        MigrationUtil::syncRoutes($this->connection, $routes, function($sql, $params) use ($queries){
            static $index = 0;
            list($expectSql, $expectParams) = $queries[$index];

            $this->assertEquals($expectSql, $sql);
            $this->assertEquals($expectParams, $params);

            $index++;
        });
    }

    public function testInsertRow()
    {
        MigrationUtil::insertRow('foo_table', ['id' => 1, 'name' => 'foo'], function($sql, $params){
            $this->assertEquals('INSERT INTO foo_table (id, name) VALUES (?, ?)', $sql);
            $this->assertEquals([1, 'foo'], $params);
        });
    }

    public function testUpdateRow()
    {
        MigrationUtil::updateRow('foo_table', ['id' => 1, 'name' => 'foo'], ['id' => 1, 'name' => 'bar'], ['name'], function($sql, $params){
            $this->assertEquals('UPDATE foo_table SET name = :name WHERE id = :id', $sql);
            $this->assertEquals(['name' => 'foo', 'id' => 1], $params);
        });
    }
}