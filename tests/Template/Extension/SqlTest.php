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

namespace Fusio\Impl\Tests\Template\Extension;

use Fusio\Impl\Template\Extension as func;
use Fusio\Impl\Template\Extension\Sql;
use Fusio\Impl\Template\Factory;

/**
 * SqlTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SqlTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        // setup the extension so that we include the file and thus load the
        // filter functions
        new Sql();
    }

    public function testFilterPrepare()
    {
        $this->assertEquals('?', func\fusio_prepare_filter('foo'));

        $factory = new Factory(false);
        $parser  = $factory->newSqlParser();

        $this->assertEquals(['foo'], $parser->getSqlParameters());
    }
}
