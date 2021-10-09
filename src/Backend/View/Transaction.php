<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Backend\View\Transaction\QueryFilter;
use Fusio\Impl\Table;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Transaction extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, QueryFilter $filter)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $condition = $filter->getCondition();

        $definition = [
            'totalResults' => $this->getTable(Table\Transaction::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Transaction::class), 'getAll'], [$startIndex, $count, 'id', Sql::SORT_DESC, $condition], [
                'id' => $this->fieldInteger('id'),
                'status' => $this->fieldInteger('status'),
                'provider' => 'provider',
                'transactionId' => 'transaction_id',
                'amount' => $this->fieldNumber('amount'),
                'updateDate' => $this->fieldDateTime('update_date'),
                'insertDate' => $this->fieldDateTime('insert_date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Transaction::class), 'get'], [$id], [
            'id' => $this->fieldInteger('id'),
            'status' => $this->fieldInteger('status'),
            'provider' => 'provider',
            'transactionId' => 'transaction_id',
            'amount' => $this->fieldNumber('amount'),
            'updateDate' => $this->fieldDateTime('update_date'),
            'insertDate' => $this->fieldDateTime('insert_date'),
        ]);

        return $this->build($definition);
    }
}
