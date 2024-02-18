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

use Fusio\Impl\Table\Generated\ScopeRow;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\ScopeTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    public function findOneByIdentifier(string $id, ?string $tenantId = null): ?ScopeRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);

        if (str_starts_with($id, '~')) {
            $condition->equals(self::COLUMN_NAME, urldecode(substr($id, 1)));
        } else {
            $condition->equals(self::COLUMN_ID, (int) $id);
        }

        return $this->findOneBy($condition);
    }

    public function getValidScopes(array $names, ?string $tenantId = null): array
    {
        $names = array_filter($names);

        if (!empty($names)) {
            $condition = Condition::withAnd();
            $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
            $condition->in(self::COLUMN_NAME, $names);
            return $this->findAll($condition, 0, 1024);
        } else {
            return [];
        }
    }

    public function getAvailableScopes(int $categoryId, ?string $tenantId = null): array
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);

        $result = $this->findAll($condition, 0, 1024, 'name', OrderBy::ASC);
        $scopes = [];
        foreach ($result as $row) {
            $scopes[$row->getName()] = $row->getDescription();
        }

        return $scopes;
    }

    public static function getNames(array $result): array
    {
        return array_map(function ($row) {
            return $row[self::COLUMN_NAME];
        }, $result);
    }
}
