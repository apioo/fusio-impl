<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\View\Dashboard;

use PSX\Nested\Builder;
use PSX\Sql\ViewAbstract;

/**
 * LatestTransactions
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
