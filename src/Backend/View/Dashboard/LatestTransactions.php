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

namespace Fusio\Impl\Backend\View\Dashboard;

use PSX\Nested\Builder;
use PSX\Sql\ViewAbstract;

/**
 * LatestTransactions
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class LatestTransactions extends ViewAbstract
{
    public function getView()
    {
        $sql = '  SELECT trans.id,
                         trans.user_id,
                         trans.plan_id,
                         trans.transaction_id,
                         trans.amount,
                         trans.insert_date
                    FROM fusio_transaction trans
                ORDER BY trans.id DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);
        $builder = new Builder($this->connection);

        $definition = [
            'entry' => $builder->doCollection($sql, [], [
                'id' => $builder->fieldInteger('id'),
                'user_id' => $builder->fieldInteger('user_id'),
                'plan_id' => $builder->fieldInteger('plan_id'),
                'transactionId' => 'transaction_id',
                'amount' => $builder->fieldCallback('amount', function($value){
                    return round($value / 100, 2);
                }),
                'date' => $builder->fieldDateTime('insert_date'),
            ]),
        ];

        return $builder->build($definition);
    }
}
