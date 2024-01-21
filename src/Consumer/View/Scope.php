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

namespace Fusio\Impl\Consumer\View;

use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope extends ViewAbstract
{
    public function getCollection(int $categoryId, int $userId, int $startIndex = 0, ?string $tenantId = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;

        $condition = Condition::withAnd();
        if (!empty($tenantId)) {
            $condition->equals('scope.tenant_id', $tenantId);
        }
        $condition->equals('scope.category_id', $categoryId ?: 1);
        $condition->equals('user_scope.user_id', $userId);

        $countSql = $this->getBaseQuery(['COUNT(*) AS cnt'], $condition);
        $querySql = $this->getBaseQuery(['scope.id', 'scope.name', 'scope.description', 'scope.metadata'], $condition, 'user_scope.id ASC');
        $querySql = $this->connection->getDatabasePlatform()->modifyLimitQuery($querySql, $count, $startIndex);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countSql, $condition->getValues(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($querySql, $condition->getValues(), [
                'id' => $builder->fieldInteger('id'),
                'name' => 'name',
                'description' => 'description',
                'metadata' => $builder->fieldJson('metadata'),
            ]),
        ];

        return $builder->build($definition);
    }

    private function getBaseQuery(array $fields, Condition $condition, ?string $orderBy = null): string
    {
        $fields  = implode(',', $fields);
        $where   = $condition->getStatement($this->connection->getDatabasePlatform());
        $orderBy = $orderBy !== null ? 'ORDER BY ' . $orderBy : '';

        return <<<SQL
    SELECT {$fields}
      FROM fusio_user_scope user_scope
INNER JOIN fusio_scope scope
        ON user_scope.scope_id = scope.id
           {$where}
           {$orderBy}
SQL;
    }
}
