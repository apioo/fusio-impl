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

namespace Fusio\Impl\Service\Rate;

use Fusio\Engine\Model;
use Fusio\Impl\Table;
use PSX\Framework\Config\ConfigInterface;
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
    private ConfigInterface $config;

    public function __construct(Table\Rate\Allocation $rateAllocationTable, Table\Log $logTable, ConfigInterface $config)
    {
        $this->rateAllocationTable = $rateAllocationTable;
        $this->logTable = $logTable;
        $this->config = $config;
    }

    public function assertLimit(string $ip, Table\Generated\OperationRow $operation, Model\AppInterface $app, Model\UserInterface $user, ?ResponseInterface $response = null): bool
    {
        $rate = $this->rateAllocationTable->getRateForRequest($this->getTenantId(), $operation, $app, $user);
        if (empty($rate)) {
            return false;
        }

        $count     = $this->getRequestCount($ip, $rate['timespan'], $app, $user);
        $rateLimit = (int) $rate['rate_limit'];

        if ($response !== null) {
            $response->setHeader('RateLimit-Limit', '' . $rateLimit);
            $response->setHeader('RateLimit-Remaining', '' . ($rateLimit - $count));
        }

        if ($count >= $rateLimit) {
            throw new StatusCode\ClientErrorException('Rate limit exceeded', 429);
        }

        return true;
    }

    private function getRequestCount(string $ip, string $timespan, Model\AppInterface $app, Model\UserInterface $user): int
    {
        if (empty($timespan)) {
            return 0;
        }

        $now  = new \DateTime();
        $past = new \DateTime();
        $past->sub(new \DateInterval($timespan));

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\LogTable::COLUMN_TENANT_ID, $this->getTenantId());

        $isAnonymous = true;
        if (!$user->isAnonymous()) {
            $condition->equals(Table\Generated\LogTable::COLUMN_USER_ID, $user->getId());
            $isAnonymous = false;
        }

        if (!$app->isAnonymous()) {
            $condition->equals(Table\Generated\LogTable::COLUMN_APP_ID, $app->getId());
            $isAnonymous = false;
        }

        if ($isAnonymous) {
            // in case we have no way to identify the user we need to use the IP
            $condition->equals(Table\Generated\LogTable::COLUMN_IP, $ip);
        }

        $condition->between(Table\Generated\LogTable::COLUMN_DATE, $past->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));

        return $this->logTable->getCount($condition);
    }

    private function getTenantId(): ?string
    {
        $tenantId = $this->config->get('fusio_tenant_id');
        if (empty($tenantId)) {
            return null;
        }

        return $tenantId;
    }
}
