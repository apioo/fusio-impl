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
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
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
            $sortOrder = Sql::SORT_DESC;
        }

        $condition = new Condition();
        $condition->equals(Table\Generated\ScopeTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);

        if (!empty($search)) {
            $condition->like(Table\Generated\ScopeTable::COLUMN_NAME, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Scope::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Scope::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(string $id)
    {
        if (str_starts_with($id, '~')) {
            $method = 'findOneByName';
            $id = urldecode(substr($id, 1));
        } else {
            $method = 'find';
            $id = (int) $id;
        }

        $definition = $this->doEntity([$this->getTable(Table\Scope::class), $method], [$id], [
            'id' => $this->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
            'name' => Table\Generated\ScopeTable::COLUMN_NAME,
            'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
            'routes' => $this->doCollection([$this->getTable(Table\Scope\Route::class), 'findByScopeId'], [new Reference('id'), 0, 1024], [
                'id' => $this->fieldInteger(Table\Generated\ScopeRoutesTable::COLUMN_ID),
                'scopeId' => $this->fieldInteger(Table\Generated\ScopeRoutesTable::COLUMN_SCOPE_ID),
                'routeId' => $this->fieldInteger(Table\Generated\ScopeRoutesTable::COLUMN_ROUTE_ID),
                'allow' => $this->fieldInteger(Table\Generated\ScopeRoutesTable::COLUMN_ALLOW),
                'methods' => Table\Generated\ScopeRoutesTable::COLUMN_METHODS,
            ]),
            'metadata' => $this->fieldJson(Table\Generated\ScopeTable::COLUMN_METADATA),
        ]);

        return $this->build($definition);
    }

    public function getCategories()
    {
        $definition = [
            'categories' => $this->doCollection([$this->getTable(Table\Category::class), 'findAll'], [null, 0, 1024, 'name', Sql::SORT_ASC], [
                'id' => $this->fieldInteger(Table\Generated\CategoryTable::COLUMN_ID),
                'name' => Table\Generated\CategoryTable::COLUMN_NAME,
                'scopes' => $this->doCollection([$this->getTable(Table\Scope::class), 'findByCategoryId'], [new Reference('id'), 0, 1024, 'name', Sql::SORT_ASC], [
                    'id' => $this->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                    'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                    'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
                ])
            ]),
        ];

        return $this->build($definition);
    }
}
