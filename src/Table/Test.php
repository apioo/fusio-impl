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

use Fusio\Impl\Table\Generated\TestRow;
use PSX\Sql\Condition;

/**
 * Test
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Test extends Generated\TestTable
{
    const STATUS_PENDING  = 0x1;
    const STATUS_SUCCESS  = 0x2;
    const STATUS_WARNING  = 0x3;
    const STATUS_ERROR  = 0x4;
    const STATUS_SKIPPED  = 0x5;
    const STATUS_DISABLED = 0x6;

    public function findOneByTenantAndId(?string $tenantId, int $categoryId, int $id): ?TestRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }
}
