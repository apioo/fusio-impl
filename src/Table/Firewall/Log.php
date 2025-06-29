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

namespace Fusio\Impl\Table\Firewall;

use Fusio\Impl\Table\Generated\FirewallLogTable;
use PSX\Sql\Condition;

/**
 * Log
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Log extends FirewallLogTable
{
    public function getResponseCodeCount(?string $tenantId, string $ip, \DateInterval $timeWindow): int
    {
        $now = new \DateTime();
        $now->sub($timeWindow);

        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_IP, $ip);
        $condition->greaterThan(self::COLUMN_INSERT_DATE, $now->format('Y-m-d H:i:s'));

        return $this->getCount($condition);
    }
}
