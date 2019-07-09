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

namespace Fusio\Impl\Tests\Service\Routes;

use Fusio\Impl\Service\Routes\Config;
use PHPUnit\Framework\TestCase;

/**
 * ConfigTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ConfigTest extends TestCase
{
    /**
     * @dataProvider operationIdProvider
     */
    public function testBuildOperationId($path, $method, $operationId)
    {
        $result = Config::buildOperationId($path, $method);

        $this->assertEquals($operationId, $result);
    }

    public function operationIdProvider()
    {
        return [
            ['/todo', 'GET', 'get.todo'],
            ['/todo', 'POST', 'post.todo'],
            ['/todo', 'PUT', 'put.todo'],
            ['/todo', 'DELETE', 'delete.todo'],
            ['/todo/:id', 'GET', 'get.todo.id'],
            ['/todo/:id', 'POST', 'post.todo.id'],
            ['/todo/:id', 'PUT', 'put.todo.id'],
            ['/todo/:id', 'DELETE', 'delete.todo.id'],
            ['/todo/:todo_id', 'GET', 'get.todo.todo_id'],
            ['/todo/:todo_id', 'POST', 'post.todo.todo_id'],
            ['/todo/:todo_id', 'PUT', 'put.todo.todo_id'],
            ['/todo/:todo_id', 'DELETE', 'delete.todo.todo_id'],
            ['/todo/$todo_id<[0-9]+>', 'GET', 'get.todo.todo_id'],
            ['/todo/$todo_id<[0-9]+>', 'POST', 'post.todo.todo_id'],
            ['/todo/$todo_id<[0-9]+>', 'PUT', 'put.todo.todo_id'],
            ['/todo/$todo_id<[0-9]+>', 'DELETE', 'delete.todo.todo_id'],
            ['/todo/:year/:category', 'GET', 'get.todo.year.category'],
        ];
    }
}
