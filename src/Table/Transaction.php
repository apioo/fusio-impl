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

namespace Fusio\Impl\Table;

use PSX\Sql\TableAbstract;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Transaction extends TableAbstract
{
    public function getName()
    {
        return 'fusio_transaction';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'plan_id' => self::TYPE_INT,
            'user_id' => self::TYPE_INT,
            'app_id' => self::TYPE_INT,
            'status' => self::TYPE_INT,
            'provider' => self::TYPE_VARCHAR,
            'transaction_id' => self::TYPE_VARCHAR,
            'remote_id' => self::TYPE_VARCHAR,
            'amount' => self::TYPE_FLOAT,
            'return_url' => self::TYPE_VARCHAR,
            'update_date' => self::TYPE_DATETIME,
            'insert_date' => self::TYPE_DATETIME,
        );
    }
    
    public function getTransactionWithPlan()
    {
        $sql = 'SELECT * 
                  FROM fusio_transaction trans
            INNER JOIN fusio_plan plan 
                    ON trans.plan_id = plan.id
                 WHERE plan.interval IS NOT NULL';

        $this->connection->fetchAll($sql);
    }
}
