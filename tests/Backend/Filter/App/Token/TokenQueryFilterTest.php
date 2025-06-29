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

namespace Fusio\Impl\Tests\Backend\Filter\App\Token;

use Fusio\Impl\Backend\Filter\App\Token\TokenQueryFilter;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Tests\Backend\Filter\FilterTestCase;

/**
 * TokenQueryFilterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenQueryFilterTest extends FilterTestCase
{
    public function testCreate()
    {
        $filter = TokenQueryFilter::from($this->createRequest([
            'from'      => '2015-08-20',
            'to'        => '2015-08-30',
            'appId'     => 1,
            'userId'    => 1,
            'status'    => 1,
            'scope'     => 'foo',
            'ip'        => '127.0.0.1',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
        $this->assertEquals(1, $filter->getAppId());
        $this->assertEquals(1, $filter->getUserId());
        $this->assertEquals(1, $filter->getStatus());
        $this->assertEquals('foo', $filter->getScope());
        $this->assertEquals('127.0.0.1', $filter->getIp());

        $condition = $filter->getCondition([DateQueryFilter::COLUMN_DATE => 'date']);

        $this->assertEquals('WHERE (date >= ? AND date <= ? AND app_id = ? AND user_id = ? AND status = ? AND scope LIKE ? AND ip LIKE ?)', $condition->getStatement());
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
        $filter = TokenQueryFilter::from($this->createRequest([
            'from' => '2015-08-30',
            'to'   => '2015-08-20',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
    }

    public function testCreateFromToExceeded()
    {
        $filter = TokenQueryFilter::from($this->createRequest([
            'from' => '2014-08-20',
            'to'   => '2015-08-30',
        ]));

        $this->assertEquals('2014-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2014-10-20', $filter->getTo()->format('Y-m-d'));
    }
}
