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

namespace Fusio\Impl\Table;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Plan extends Generated\PlanTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    /**
     * Returns an array of plans which are currently active for the provided user
     */
    public function getActivePlansForUser(int $userId): array
    {
        $now = $this->connection->getDatabasePlatform()->getNowExpression();

        $query = 'SELECT plan.* 
                    FROM fusio_transaction trx
              INNER JOIN fusio_plan plan
                      ON plan.id = trx.plan_id
                   WHERE trx.user_id = :user_id 
                     AND ' . $now . ' >= trx.period_start
                     AND ' . $now . ' <= trx.period_end';
        $result = $this->connection->fetchAllAssociative($query, [
            'user_id' => $userId,
        ]);

        $plans = [];
        foreach ($result as $row) {
            $plans[] = $this->newRecord($row);
        }
        return $plans;
    }
}
