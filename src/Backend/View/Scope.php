<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Scope extends ViewAbstract
{
    public function getCollection(int $categoryId, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\ScopeTable::COLUMN_ID;
        }

        if ($sortOrder === null) {
            $sortOrder = OrderBy::DESC;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ScopeTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);

        if (!empty($search)) {
            $condition->like(Table\Generated\ScopeTable::COLUMN_NAME, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Scope::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Scope::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
                'metadata' => $builder->fieldJson(Table\Generated\ScopeTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Scope::class), 'findOneByIdentifier'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
            'name' => Table\Generated\ScopeTable::COLUMN_NAME,
            'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
            'metadata' => $builder->fieldJson(Table\Generated\ScopeTable::COLUMN_METADATA),
            'routes' => $builder->doCollection([$this->getTable(Table\Scope\Operation::class), 'findByScopeId'], [new Reference('id'), 0, 1024], [
                'id' => $builder->fieldInteger(Table\Generated\ScopeOperationTable::COLUMN_ID),
                'scopeId' => $builder->fieldInteger(Table\Generated\ScopeOperationTable::COLUMN_SCOPE_ID),
                'operationId' => $builder->fieldInteger(Table\Generated\ScopeOperationTable::COLUMN_OPERATION_ID),
                'allow' => $builder->fieldInteger(Table\Generated\ScopeOperationTable::COLUMN_ALLOW),
                'methods' => Table\Generated\ScopeOperationTable::COLUMN_METHODS,
            ]),
        ]);

        return $builder->build($definition);
    }

    public function getCategories()
    {
        $builder = new Builder($this->connection);

        $definition = [
            'categories' => $builder->doCollection([$this->getTable(Table\Category::class), 'findAll'], [null, 0, 1024, 'name', OrderBy::ASC], [
                'id' => $builder->fieldInteger(Table\Generated\CategoryTable::COLUMN_ID),
                'name' => Table\Generated\CategoryTable::COLUMN_NAME,
                'scopes' => $builder->doCollection([$this->getTable(Table\Scope::class), 'findByCategoryId'], [new Reference('id'), 0, 1024, 'name', OrderBy::ASC], [
                    'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                    'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                    'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
                ])
            ]),
        ];

        return $builder->build($definition);
    }
}
