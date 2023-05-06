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

use Fusio\Impl\Table\Generated;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\PlanScopeTable
{
    public function deleteAllFromPlan(int $planId)
    {
        $sql = 'DELETE FROM fusio_plan_scope
                      WHERE plan_id = :id';

        $this->connection->executeQuery($sql, ['id' => $planId]);
    }

    public function getAvailableScopes(int $planId): array
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_plan_scope plan_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = plan_scope.scope_id
                     WHERE plan_scope.plan_id = :plan_id
                  ORDER BY scope.id ASC';

        return $this->connection->fetchAllAssociative($sql, ['plan_id' => $planId]) ?: [];
    }
}
