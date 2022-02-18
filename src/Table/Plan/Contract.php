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
use Fusio\Impl\Table\Generated;

/**
 * Contract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Contract extends Generated\PlanContractTable
{
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 2;
    const STATUS_CLOSED = 3;
    const STATUS_DELETED = 4;

    public function getName(): string
    {
        return 'fusio_plan_contract';
    }

    public function getColumns(): array
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'user_id' => self::TYPE_INT,
            'plan_id' => self::TYPE_INT,
            'status' => self::TYPE_INT,
            'amount' => self::TYPE_FLOAT,
            'points' => self::TYPE_INT,
            'period_type' => self::TYPE_INT,
            'insert_date' => self::TYPE_DATETIME,
        );
    }
    
    public function getActiveContracts()
    {
        return $this->findBy(new Condition(['status', '=', self::STATUS_ACTIVE]));
    }
}
