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

namespace Fusio\Impl\Table\Rate;

use Fusio\Engine\Model;
use Fusio\Impl\Table\Generated;
use Fusio\Impl\Table\Rate;

/**
 * Allocation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Allocation extends Generated\RateAllocationTable
{
    public function deleteAllFromRate($rateId): void
    {
        $sql = 'DELETE FROM fusio_rate_allocation 
                      WHERE rate_id = :rate_id';

        $this->connection->executeStatement($sql, ['rate_id' => $rateId]);
    }

    public function getRateForRequest(Generated\OperationRow $operation, Model\AppInterface $app, Model\UserInterface $user): array
    {
        $sql = '    SELECT rate.rate_limit,
                           rate.timespan
                      FROM fusio_rate_allocation rate_allocation
                INNER JOIN fusio_rate rate
                        ON rate_allocation.rate_id = rate.id 
                     WHERE rate.status = :status
                       AND (rate_allocation.operation_id IS NULL OR rate_allocation.operation_id = :operation_id)
                       AND (rate_allocation.user_id IS NULL OR rate_allocation.user_id = :user_id)
                       AND (rate_allocation.plan_id IS NULL OR rate_allocation.plan_id = :plan_id)
                       AND (rate_allocation.app_id IS NULL OR rate_allocation.app_id = :app_id)
                       AND (rate_allocation.authenticated IS NULL OR rate_allocation.authenticated = :authenticated)';

        $params = [
            'status' => Rate::STATUS_ACTIVE,
            'operation_id' => $operation->getId(),
            'user_id' => $user->getId(),
            'plan_id' => $user->getPlanId(),
            'app_id' => $app->getId(),
            'authenticated' => $app->isAnonymous() ? 0 : 1,
        ];

        $sql.= ' ORDER BY rate.priority DESC';

        $row = $this->connection->fetchAssociative($sql, $params);
        if (empty($row)) {
            throw new \RuntimeException('Could not find rate for request');
        }

        return $row;
    }
}
