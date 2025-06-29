<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Rate;

use Fusio\Engine\Model;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Http\ResponseInterface;
use PSX\Sql\Condition;

/**
 * Limiter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Limiter
{
    private Table\Rate\Allocation $rateAllocationTable;
    private Table\Log $logTable;
    private FrameworkConfig $frameworkConfig;

    public function __construct(Table\Rate\Allocation $rateAllocationTable, Table\Log $logTable, FrameworkConfig $frameworkConfig)
    {
        $this->rateAllocationTable = $rateAllocationTable;
        $this->logTable = $logTable;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function assertLimit(string $ip, Table\Generated\OperationRow $operation, Model\AppInterface $app, Model\UserInterface $user, ?ResponseInterface $response = null): bool
    {
        $rate = $this->rateAllocationTable->getRateForRequest($this->frameworkConfig->getTenantId(), $operation, $app, $user);
        if (empty($rate)) {
            return false;
        }

        $count     = $this->getRequestCount($ip, $rate['timespan'], $user);
        $rateLimit = (int) $rate['rate_limit'];

        if ($response !== null) {
            $response->setHeader('RateLimit-Limit', '' . $rateLimit);
            $response->setHeader('RateLimit-Remaining', '' . ($rateLimit - $count));
        }

        if ($count >= $rateLimit) {
            throw new StatusCode\TooManyRequestsException('Rate limit exceeded', 60 * 15);
        }

        return true;
    }

    private function getRequestCount(string $ip, string $timespan, Model\UserInterface $user): int
    {
        if (empty($timespan)) {
            return 0;
        }

        $now  = new \DateTime();
        $past = new \DateTime();
        $past->sub(new \DateInterval($timespan));

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\LogTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());

        if (!$user->isAnonymous()) {
            $condition->equals(Table\Generated\LogTable::COLUMN_USER_ID, $user->getId());
        } else {
            // in case we have no way to identify the user we need to use the IP
            $condition->equals(Table\Generated\LogTable::COLUMN_IP, $ip);
        }

        $condition->between(Table\Generated\LogTable::COLUMN_DATE, $past->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));

        return $this->logTable->getCount($condition);
    }
}
