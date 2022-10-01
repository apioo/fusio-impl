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
use PSX\Sql\Fields;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Schema extends ViewAbstract
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
            $sortBy = 'id';
        }

        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_DESC;
        }

        $condition = new Condition();
        $condition->equals('category_id', $categoryId ?: 1);
        $condition->equals('status', Table\Schema::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Schema::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Schema::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
                'name' => Table\Generated\SchemaTable::COLUMN_NAME,
                'metadata' => $this->fieldJson(Table\Generated\SchemaTable::COLUMN_METADATA),
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

        $definition = $this->doEntity([$this->getTable(Table\Schema::class), $method], [$id], [
            'id' => $this->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
            'status' => $this->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
            'name' => Table\Generated\SchemaTable::COLUMN_NAME,
            'metadata' => $this->fieldJson(Table\Generated\SchemaTable::COLUMN_METADATA),
            'source' => Table\Generated\SchemaTable::COLUMN_SOURCE,
            'form' => $this->fieldJson(Table\Generated\SchemaTable::COLUMN_FORM),
        ]);

        return $this->build($definition);
    }

    public function getEntityWithForm($name)
    {
        if (is_numeric($name)) {
            $method = 'find';
        } else {
            $method = 'findOneByName';
        }

        $definition = $this->doEntity([$this->getTable(Table\Schema::class), $method], [$name], [
            'id' => $this->fieldInteger(Table\Generated\SchemaTable::COLUMN_ID),
            'status' => $this->fieldInteger(Table\Generated\SchemaTable::COLUMN_STATUS),
            'name' => Table\Generated\SchemaTable::COLUMN_NAME,
            'form' => $this->fieldJson(Table\Generated\SchemaTable::COLUMN_FORM),
        ]);

        return $this->build($definition);
    }
}
