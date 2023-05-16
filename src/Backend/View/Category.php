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
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Category
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Category extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\CategoryTable::COLUMN_NAME;
        }

        if ($sortOrder === null) {
            $sortOrder = OrderBy::ASC;
        }

        $condition = Condition::withAnd();
        $condition->in(Table\Generated\CategoryTable::COLUMN_STATUS, [Table\Category::STATUS_ACTIVE]);

        if (!empty($search)) {
            $condition->like(Table\Generated\CategoryTable::COLUMN_NAME, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Category::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Category::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\CategoryTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\CategoryTable::COLUMN_STATUS),
                'name' => Table\Generated\CategoryTable::COLUMN_NAME,
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity($id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Category::class), 'find'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\CategoryTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\CategoryTable::COLUMN_STATUS),
            'name' => Table\Generated\CategoryTable::COLUMN_NAME,
        ]);

        return $builder->build($definition);
    }
}
