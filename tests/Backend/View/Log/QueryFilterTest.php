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

namespace Fusio\Impl\Tests\Backend\View\Log;

use Fusio\Impl\Backend\View\Log\QueryFilter;
use Fusio\Impl\Tests\Backend\View\FilterTestCase;

/**
 * QueryFilterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class QueryFilterTest extends FilterTestCase
{
    public function testCreate()
    {
        $filter = QueryFilter::create($this->createRequest([
            'from'      => '2015-08-20',
            'to'        => '2015-08-30',
            'routeId'   => 1,
            'appId'     => 1,
            'userId'    => 1,
            'ip'        => '127.0.0.1',
            'userAgent' => 'Foo-App',
            'method'    => 'POST',
            'path'      => '/foo',
            'header'    => 'text/xml',
            'body'      => '<foo />',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
        $this->assertEquals(1, $filter->getRouteId());
        $this->assertEquals(1, $filter->getAppId());
        $this->assertEquals(1, $filter->getUserId());
        $this->assertEquals('127.0.0.1', $filter->getIp());
        $this->assertEquals('Foo-App', $filter->getUserAgent());
        $this->assertEquals('POST', $filter->getMethod());
        $this->assertEquals('/foo', $filter->getPath());
        $this->assertEquals('text/xml', $filter->getHeader());
        $this->assertEquals('<foo />', $filter->getBody());

        $condition = $filter->getCondition();

        $this->assertEquals('WHERE (date >= ? AND date <= ? AND route_id = ? AND app_id = ? AND user_id = ? AND ip LIKE ? AND user_agent LIKE ? AND method = ? AND path LIKE ? AND header LIKE ? AND body LIKE ?)', $condition->getStatment());
        $this->assertEquals([
            '2015-08-20 00:00:00',
            '2015-08-30 23:59:59',
            1,
            1,
            1,
            '127.0.0.1',
            '%Foo-App%',
            'POST',
            '/foo%',
            '%text/xml%',
            '%<foo />%',
        ], $condition->getValues());
    }

    public function testCreateFromLargerToFlip()
    {
        $filter = QueryFilter::create($this->createRequest([
            'from' => '2015-08-30',
            'to'   => '2015-08-20',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
    }

    public function testCreateFromToExceeded()
    {
        $filter = QueryFilter::create($this->createRequest([
            'from' => '2014-08-20',
            'to'   => '2015-08-30',
        ]));

        $this->assertEquals('2014-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2014-10-20', $filter->getTo()->format('Y-m-d'));
    }

    public function testCreateSearchIp()
    {
        $filter = QueryFilter::create($this->createRequest([
            'search' => '93.223.172.206'
        ]));

        $this->assertEquals('93.223.172.206', $filter->getIp());
    }

    public function testCreateSearchPath()
    {
        $filter = QueryFilter::create($this->createRequest([
            'search' => '/foo/bar'
        ]));

        $this->assertEquals('/foo/bar', $filter->getPath());
    }

    public function testCreateSearchMethod()
    {
        $filter = QueryFilter::create($this->createRequest([
            'search' => 'GET'
        ]));

        $this->assertEquals('GET', $filter->getMethod());
    }

    public function testCreateSearchHeader()
    {
        $filter = QueryFilter::create($this->createRequest([
            'search' => 'User-Agent: Foo'
        ]));

        $this->assertEquals('User-Agent: Foo', $filter->getHeader());
    }

    public function testCreateSearchBody()
    {
        $filter = QueryFilter::create($this->createRequest([
            'search' => '{"foo": "bar"}'
        ]));

        $this->assertEquals('{"foo": "bar"}', $filter->getBody());
    }
}
