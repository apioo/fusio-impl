<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Backend\Filter\Log;

use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Backend\Filter\Log\LogQueryFilter;
use Fusio\Impl\Tests\Backend\Filter\FilterTestCase;

/**
 * LogQueryFilterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LogQueryFilterTest extends FilterTestCase
{
    public function testCreate()
    {
        $filter = LogQueryFilter::from($this->createRequest([
            'from' => '2015-08-20',
            'to' => '2015-08-30',
            'operationId' => 1,
            'appId' => 1,
            'userId' => 1,
            'ip' => '127.0.0.1',
            'userAgent' => 'Foo-App',
            'method' => 'POST',
            'path' => '/foo',
            'header' => 'text/xml',
            'body' => '<foo />',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
        $this->assertEquals(1, $filter->getOperationId());
        $this->assertEquals(1, $filter->getAppId());
        $this->assertEquals(1, $filter->getUserId());
        $this->assertEquals('127.0.0.1', $filter->getIp());
        $this->assertEquals('Foo-App', $filter->getUserAgent());
        $this->assertEquals('POST', $filter->getMethod());
        $this->assertEquals('/foo', $filter->getPath());
        $this->assertEquals('text/xml', $filter->getHeader());
        $this->assertEquals('<foo />', $filter->getBody());

        $condition = $filter->getCondition([DateQueryFilter::COLUMN_DATE => 'date']);

        $this->assertEquals('WHERE (date >= ? AND date <= ? AND operation_id = ? AND app_id = ? AND user_id = ? AND ip LIKE ? AND user_agent LIKE ? AND method = ? AND path LIKE ? AND header LIKE ? AND body LIKE ?)', $condition->getStatement());
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
        $filter = LogQueryFilter::from($this->createRequest([
            'from' => '2015-08-30',
            'to'   => '2015-08-20',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
    }

    public function testCreateFromToExceeded()
    {
        $filter = LogQueryFilter::from($this->createRequest([
            'from' => '2014-08-20',
            'to'   => '2015-08-30',
        ]));

        $this->assertEquals('2014-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2014-10-20', $filter->getTo()->format('Y-m-d'));
    }

    public function testCreateSearchIp()
    {
        $filter = LogQueryFilter::from($this->createRequest([
            'search' => '93.223.172.206'
        ]));

        $this->assertEquals('93.223.172.206', $filter->getIp());
    }

    public function testCreateSearchPath()
    {
        $filter = LogQueryFilter::from($this->createRequest([
            'search' => '/foo/bar'
        ]));

        $this->assertEquals('/foo/bar', $filter->getPath());
    }

    public function testCreateSearchMethod()
    {
        $filter = LogQueryFilter::from($this->createRequest([
            'search' => 'GET'
        ]));

        $this->assertEquals('GET', $filter->getMethod());
    }

    public function testCreateSearchHeader()
    {
        $filter = LogQueryFilter::from($this->createRequest([
            'search' => 'User-Agent: Foo'
        ]));

        $this->assertEquals('User-Agent: Foo', $filter->getHeader());
    }

    public function testCreateSearchBody()
    {
        $filter = LogQueryFilter::from($this->createRequest([
            'search' => '{"foo": "bar"}'
        ]));

        $this->assertEquals('{"foo": "bar"}', $filter->getBody());
    }
}
