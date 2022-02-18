<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Table\Generated;

/**
 * Invoice
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Invoice extends Generated\PlanInvoiceTable
{
    public const STATUS_OPEN = 0;
    public const STATUS_PAYED = 1;
    public const STATUS_DELETED = 2;

    public function findLastInvoiceByContract($contractId)
    {
        $condition = new Condition(['contract_id', '=', $contractId]);
        $result    = $this->findBy($condition, 0, 1, 'id', Sql::SORT_DESC);

        return $result[0] ?? null;
    }

    public function findPlanByInvoiceId($invoiceId)
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
