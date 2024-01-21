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

namespace Fusio\Impl\Table;

use Fusio\Impl\Table\Generated\AppRow;
use Fusio\Impl\Table\Generated\OperationRow;
use PSX\Sql\Condition;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class App extends Generated\AppTable
{
    public const STATUS_ACTIVE      = 0x1;
    public const STATUS_PENDING     = 0x2;
    public const STATUS_DEACTIVATED = 0x3;
    public const STATUS_DELETED     = 0x4;

    public function findOneByIdentifier(string $id, ?string $tenantId = null): ?AppRow
    {
        $condition = Condition::withAnd();
        if (!empty($tenantId)) {
            $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        }

        if (str_starts_with($id, '~')) {
            $condition->equals(self::COLUMN_NAME, urldecode(substr($id, 1)));
        } else {
            $condition->equals(self::COLUMN_ID, (int) $id);
        }

        return $this->findOneBy($condition);
    }

    public function findOneByAppKeyAndSecret(string $appKey, string $appSecret, ?string $tenantId = null): ?AppRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_APP_KEY, $appKey);
        $condition->equals(self::COLUMN_APP_SECRET, $appSecret);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);

        if (!empty($tenantId)) {
            $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        }

        return $this->findOneBy($condition);
    }
}
