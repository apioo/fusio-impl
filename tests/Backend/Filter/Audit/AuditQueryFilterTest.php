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

namespace Fusio\Impl\Tests\Backend\Filter\Audit;

use Fusio\Impl\Backend\Filter\Audit\AuditQueryFilter;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Tests\Backend\Filter\FilterTestCase;

/**
 * AuditQueryFilterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuditQueryFilterTest extends FilterTestCase
{
    public function testCreate()
    {
        $filter = AuditQueryFilter::from($this->createRequest([
            'from'    => '2015-08-20',
            'to'      => '2015-08-30',
            'appId'   => 1,
            'userId'  => 1,
            'event'   => 'create',
            'ip'      => '127.0.0.1',
            'message' => 'foo',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
        $this->assertEquals(1, $filter->getAppId());
        $this->assertEquals(1, $filter->getUserId());
        $this->assertEquals('create', $filter->getEvent());
        $this->assertEquals('127.0.0.1', $filter->getIp());

        $condition = $filter->getCondition([DateQueryFilter::COLUMN_DATE => 'date']);

        $this->assertEquals('WHERE (date >= ? AND date <= ? AND app_id = ? AND user_id = ? AND event LIKE ? AND ip LIKE ? AND message LIKE ?)', $condition->getStatement());
        $this->assertEquals([
            '2015-08-20 00:00:00',
            '2015-08-30 23:59:59',
            1,
            1,
            '%create%',
            '127.0.0.1',
            '%foo%',
        ], $condition->getValues());
    }

    public function testCreateSearchIp()
    {
        $filter = AuditQueryFilter::from($this->createRequest([
            'search' => '93.223.172.206'
        ]));

        $this->assertEquals('93.223.172.206', $filter->getIp());
    }

    public function testCreateSearchEvent()
    {
        $filter = AuditQueryFilter::from($this->createRequest([
            'search' => 'create'
        ]));

        $this->assertEquals('create', $filter->getMessage());
    }
}
