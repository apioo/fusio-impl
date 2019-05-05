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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Transaction extends ViewAbstract
{
    public function getCollection($userId, $startIndex = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = new Condition();
        $condition->equals('contract.user_id', $userId);

        $countSql = $this->getBaseQuery(['COUNT(*) AS cnt'], $condition);
        $querySql = $this->getBaseQuery(['transact.id', 'transact.status', 'transact.provider', 'transact.transaction_id', 'transact.amount', 'transact.update_date', 'transact.insert_date'], $condition);
        $querySql = $this->connection->getDatabasePlatform()->modifyLimitQuery($querySql, $count, $startIndex);

        $condition = new Condition();
        $condition->equals('contract.user_id', $userId);

        $definition = [
            'totalResults' => $this->doValue($countSql, $condition->getValues(), $this->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection($querySql, $condition->getValues(), [
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

    public function getEntity($userId, $transactionId)
    {
        $condition = new Condition();
        $condition->equals('transact.id', $transactionId);
        $condition->equals('contract.user_id', $userId);

        $querySql = $this->getBaseQuery(['transact.id', 'contract.plan_id', 'transact.status', 'transact.provider', 'transact.transaction_id', 'transact.amount', 'transact.update_date', 'transact.insert_date'], $condition);

        $definition = $this->doEntity($querySql, $condition->getValues(), [
            'id' => $this->fieldInteger('id'),
            'plan' => $this->doEntity([$this->getTable(Plan::class), 'getEntity'], [$userId, new Reference('plan_id')], [
                'id' => $this->fieldInteger('id'),
                'name' => 'name',
                'description' => 'description',
                'price' => $this->fieldNumber('price'),
                'points' => $this->fieldInteger('points'),
            ]),
            'status' => $this->fieldInteger('status'),
            'provider' => 'provider',
            'transactionId' => 'transaction_id',
            'amount' => $this->fieldNumber('amount'),
            'updateDate' => $this->fieldDateTime('update_date'),
            'insertDate' => $this->fieldDateTime('insert_date'),
        ]);

        return $this->build($definition);
    }

    /**
     * @param array $fields
     * @param \PSX\Sql\Condition $condition
     * @param string $orderBy
     * @return string
     */
    private function getBaseQuery(array $fields, Condition $condition, $orderBy = null)
    {
        $fields  = implode(',', $fields);
        $where   = $condition->getStatment($this->connection->getDatabasePlatform());
        $orderBy = $orderBy !== null ? 'ORDER BY ' . $orderBy : '';

        return <<<SQL
    SELECT {$fields}
      FROM fusio_transaction transact
INNER JOIN fusio_plan_invoice invoice
        ON invoice.id = transact.invoice_id
INNER JOIN fusio_plan_contract contract
        ON contract.id = invoice.contract_id
           {$where}
           {$orderBy}
SQL;
    }
}
