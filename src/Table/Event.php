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

use Fusio\Impl\Table\Generated\CronjobRow;
use Fusio\Impl\Table\Generated\EventRow;
use PSX\Sql\Condition;

/**
 * Event
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Event extends Generated\EventTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    public function findOneByIdentifier(?string $tenantId, int $categoryId, string $id): ?EventRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndName($tenantId, $categoryId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, $categoryId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, ?int $categoryId, int $id): ?EventRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        if ($categoryId !== null) {
            $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        }
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, ?int $categoryId, string $name): ?EventRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        if ($categoryId !== null) {
            $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        }
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }
}
