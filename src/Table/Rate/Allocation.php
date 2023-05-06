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

namespace Fusio\Impl\Table\Rate;

use Fusio\Engine\Model;
use Fusio\Impl\Table\Generated;
use Fusio\Impl\Table\Rate;

/**
 * Allocation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Allocation extends Generated\RateAllocationTable
{
    public function deleteAllFromRate($rateId)
    {
        $sql = 'DELETE FROM fusio_rate_allocation 
                      WHERE rate_id = :rate_id';

        $this->connection->executeStatement($sql, ['rate_id' => $rateId]);
    }

    public function getRateForRequest(int $routeId, Model\AppInterface $app, Model\UserInterface $user)
    {
        $sql = '    SELECT rate.rate_limit,
                           rate.timespan
                      FROM fusio_rate_allocation rate_allocation
                INNER JOIN fusio_rate rate
                        ON rate_allocation.rate_id = rate.id 
                     WHERE rate.status = :status
                       AND (rate_allocation.route_id IS NULL OR rate_allocation.route_id = :route_id)
                       AND (rate_allocation.user_id IS NULL OR rate_allocation.user_id = :user_id)
                       AND (rate_allocation.plan_id IS NULL OR rate_allocation.plan_id = :plan_id)
                       AND (rate_allocation.app_id IS NULL OR rate_allocation.app_id = :app_id)
                       AND (rate_allocation.authenticated IS NULL OR rate_allocation.authenticated = :authenticated)';

        $params = [
            'status' => Rate::STATUS_ACTIVE,
            'route_id' => $routeId,
            'user_id' => $user->getId(),
            'plan_id' => $user->getPlanId(),
            'app_id' => $app->getId(),
            'authenticated' => $app->isAnonymous() ? 0 : 1,
        ];

        $sql.= ' ORDER BY rate.priority DESC';

        return $this->connection->fetchAssociative($sql, $params);
    }
}
