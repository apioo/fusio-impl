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

namespace Fusio\Impl\Table\Plan;

use PSX\Sql\Condition;
use PSX\Sql\Sql;
use PSX\Sql\TableAbstract;

/**
 * Invoice
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Invoice extends TableAbstract
{
    const STATUS_OPEN = 0;
    const STATUS_PAYED = 1;
    const STATUS_DELETED = 2;

    public function getName()
    {
        return 'fusio_plan_invoice';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'contract_id' => self::TYPE_INT,
            'user_id' => self::TYPE_INT,
            'prev_id' => self::TYPE_INT, // id of the previous invoice
            'display_id' => self::TYPE_VARCHAR,
            'status' => self::TYPE_INT,
            'amount' => self::TYPE_FLOAT,
            'points' => self::TYPE_INT,
            'from_date' => self::TYPE_DATE,
            'to_date' => self::TYPE_DATE,
            'pay_date' => self::TYPE_DATETIME,
            'insert_date' => self::TYPE_DATETIME,
        );
    }

    public function getLastInvoiceByContract($contractId)
    {
        $condition = new Condition(['contract_id', '=', $contractId]);
        $result    = $this->getBy($condition, null, 0, 1, 'id', Sql::SORT_DESC);

        return $result[0] ?? null;
    }

    public function getPlanByInvoiceId($invoiceId)
    {
        $sql = 'SELECT plan.id,
                       plan.name,
                       plan.period_type,
                       invoice.amount,
                       invoice.points
                  FROM fusio_plan_invoice invoice
            INNER JOIN fusio_plan_contract contract
                    ON invoice.contract_id = contract.id
            INNER JOIN fusio_plan plan
                    ON contract.plan_id = plan.id
                 WHERE invoice.id = :id';

        return $this->connection->fetchAssoc($sql, ['id' => $invoiceId]);
    }
}
