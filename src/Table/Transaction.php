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

namespace Fusio\Impl\Table;

use Fusio\Impl\Table\Generated\ScopeRow;
use Fusio\Impl\Table\Generated\TransactionRow;
use PSX\Sql\Condition;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Transaction extends Generated\TransactionTable
{
    public function findOneByIdentifier(?string $tenantId, string $id): ?TransactionRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndTransactionId($tenantId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, int $id): ?TransactionRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndTransactionId(?string $tenantId, string $transactionId): ?TransactionRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_TRANSACTION_ID, $transactionId);

        return $this->findOneBy($condition);
    }
}
