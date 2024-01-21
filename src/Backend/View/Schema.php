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

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Schema extends ViewAbstract
{
    public function getCollection(int $categoryId, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null, ?string $tenantId = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = 'id';
        }

        if ($sortOrder === null) {
            $sortOrder = OrderBy::DESC;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\SchemaTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Table\Generated\SchemaTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);
        $condition->equals(Table\Generated\SchemaTable::COLUMN_STATUS, Table\Schema::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\SchemaTable::COLUMN_NAME, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Schema::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Schema::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
                'name' => Table\Generated\SchemaTable::COLUMN_NAME,
                'metadata' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ?string $tenantId = null)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Schema::class), 'findOneByIdentifier'], [$id, $tenantId], [
            'id' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
            'name' => Table\Generated\SchemaTable::COLUMN_NAME,
            'metadata' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_METADATA),
            'source' => Table\Generated\SchemaTable::COLUMN_SOURCE,
            'form' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_FORM),
        ]);

        return $builder->build($definition);
    }

    public function getEntityWithForm($name)
    {
        if (is_numeric($name)) {
            $method = 'find';
        } else {
            $method = 'findOneByName';
        }

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Schema::class), $method], [$name], [
            'id' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
            'name' => Table\Generated\SchemaTable::COLUMN_NAME,
            'form' => $builder->fieldJson(Table\Generated\SchemaTable::COLUMN_FORM),
        ]);

        return $builder->build($definition);
    }
}
