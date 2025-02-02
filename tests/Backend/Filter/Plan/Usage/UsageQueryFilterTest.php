<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Tests\Backend\Filter\Plan\Usage;

use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Backend\Filter\Plan\Usage\UsageQueryFilter;
use Fusio\Impl\Tests\Backend\Filter\FilterTestCase;

/**
 * UsageQueryFilterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UsageQueryFilterTest extends FilterTestCase
{
    public function testCreate()
    {
        $filter = UsageQueryFilter::from($this->createRequest([
            'from' => '2015-08-20',
            'to' => '2015-08-30',
            'operationId' => 1,
            'appId' => 1,
            'userId' => 1,
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
        $this->assertEquals(1, $filter->getOperationId());
        $this->assertEquals(1, $filter->getAppId());
        $this->assertEquals(1, $filter->getUserId());

        $condition = $filter->getCondition([DateQueryFilter::COLUMN_DATE => 'insert_date']);

        $this->assertEquals('WHERE (insert_date >= ? AND insert_date <= ? AND route_id = ? AND user_id = ? AND app_id = ?)', $condition->getStatement());
        $this->assertEquals([
            '2015-08-20 00:00:00',
            '2015-08-30 23:59:59',
            1,
            1,
            1,
        ], $condition->getValues());
    }
}
