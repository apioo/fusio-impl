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
        // update path route so that we update this route
        $this->connection->executeUpdate('UPDATE fusio_routes SET priority = :priority WHERE path = :path', ['priority' => 0x10000000, 'path' => '/backend/action']);

        // delete path route so that we create this route
        $this->connection->executeUpdate('DELETE FROM fusio_routes WHERE path = :path', ['path' => '/backend/schema']);

        $queries = [
            [
                'UPDATE fusio_routes SET priority = :priority WHERE id = :id',
                ['priority' => 268435511, 'id' => 2],
            ],
            [
                'INSERT INTO fusio_routes (status, priority, methods, path, controller) VALUES (?, ?, ?, ?, ?)',
                [1, 268435476, 'ANY', '/backend/schema', Backend\Api\Schema\Collection::class],
            ],
            [
                'INSERT INTO fusio_scope_routes (scope_id, route_id, allow, methods) VALUES (?, ?, ?, ?)',
                [1, 93, 1, 'GET|POST|PUT|PATCH|DELETE'],
            ]
        ];

        MigrationUtil::syncRoutes($this->connection, function($sql, $params) use ($queries){
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