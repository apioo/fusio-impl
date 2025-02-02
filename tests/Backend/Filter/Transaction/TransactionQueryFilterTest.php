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

namespace Fusio\Impl\Tests\Backend\Filter\Transaction;

use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Backend\Filter\Transaction\TransactionQueryFilter;
use Fusio\Impl\Tests\Backend\Filter\FilterTestCase;

/**
 * TransactionQueryFilterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TransactionQueryFilterTest extends FilterTestCase
{
    public function testCreate()
    {
        $filter = TransactionQueryFilter::from($this->createRequest([
            'from'      => '2015-08-20',
            'to'        => '2015-08-30',
            'invoiceId' => 1,
            'status'    => 1,
            'provider'  => 'Paypal',
        ]));

        $this->assertEquals('2015-08-20', $filter->getFrom()->format('Y-m-d'));
        $this->assertEquals('2015-08-30', $filter->getTo()->format('Y-m-d'));
        $this->assertEquals(1, $filter->getInvoiceId());
        $this->assertEquals(1, $filter->getStatus());
        $this->assertEquals('Paypal', $filter->getProvider());

        $condition = $filter->getCondition([DateQueryFilter::COLUMN_DATE => 'insert_date']);

        $this->assertEquals('WHERE (insert_date >= ? AND insert_date <= ? AND invoice_id = ? AND status = ? AND provider LIKE ?)', $condition->getStatement());
        $this->assertEquals([
            '2015-08-20 00:00:00',
            '2015-08-30 23:59:59',
            1,
            1,
            'Paypal',
        ], $condition->getValues());
    }
}
