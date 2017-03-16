<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\View\App\Token;

use Fusio\Impl\Backend\View\App\Token\QueryFilter;

/**
 * QueryFilterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class QueryFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $filter = QueryFilter::create([
            'from'      => '2015-08-20',
            'to'        => '2015-08-30',
            'appId'     => 1,
            'userId'    => 1,
            'status'    => 1,
            'scope'     => 'foo',
            'ip'        => '127.0.0.1',
        ]);

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
        $this->assertEquals(1, $filter->getAppId());
        $this->assertEquals(1, $filter->getUserId());
        $this->assertEquals(1, $filter->getStatus());
        $this->assertEquals('foo', $filter->getScope());
        $this->assertEquals('127.0.0.1', $filter->getIp());

        $condition = $filter->getCondition();

        $this->assertEquals('WHERE (date >= ? AND date <= ? AND appId = ? AND userId = ? AND status = ? AND scope LIKE ? AND ip LIKE ?)', $condition->getStatment());
        $this->assertEquals([
            '2015-08-20 00:00:00',
            '2015-08-30 23:59:59',
            1,
            1,
            1,
            '%foo%',
            '127.0.0.1',
        ], $condition->getValues());
    }

    public function testCreateFromLargerToFlip()
    {
        $filter = QueryFilter::create([
            'from' => '2015-08-30',
            'to'   => '2015-08-20',
        ]);

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
    }

    public function testCreateFromToExceeded()
    {
        $filter = QueryFilter::create([
            'from' => '2014-08-20',
            'to'   => '2015-08-30',
        ]);

        $this->assertEquals('2014-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2014-10-20', $filter->getTo()->format('Y-m-d'));
    }

    public function testCreateSearchIp()
    {
        $filter = QueryFilter::create([
            'search' => '93.223.172.206'
        ]);

        $this->assertEquals('93.223.172.206', $filter->getIp());
    }

    public function testCreateSearchScope()
    {
        $filter = QueryFilter::create([
            'search' => 'foo'
        ]);

        $this->assertEquals('foo', $filter->getScope());
    }
}
