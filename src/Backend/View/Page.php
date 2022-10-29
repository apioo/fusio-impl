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
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Page
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Page extends ViewAbstract
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
            $sortBy = Table\Generated\PageTable::COLUMN_SLUG;
        }
        
        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_ASC;
        }

        $condition = new Condition();
        $condition->in(Table\Generated\PageTable::COLUMN_STATUS, [Table\Page::STATUS_VISIBLE, Table\Page::STATUS_INVISIBLE]);

        if (!empty($search)) {
            $condition->like(Table\Generated\PageTable::COLUMN_TITLE, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Page::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Page::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\PageTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\PageTable::COLUMN_STATUS),
                'title' => Table\Generated\PageTable::COLUMN_TITLE,
                'slug' => Table\Generated\PageTable::COLUMN_SLUG,
                'metadata' => $this->fieldJson(Table\Generated\PageTable::COLUMN_METADATA),
                'date' => $this->fieldDateTime(Table\Generated\PageTable::COLUMN_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Page::class), 'find'], [$id], [
            'id' => $this->fieldInteger(Table\Generated\PageTable::COLUMN_ID),
            'status' => $this->fieldInteger(Table\Generated\PageTable::COLUMN_STATUS),
            'title' => Table\Generated\PageTable::COLUMN_TITLE,
            'slug' => Table\Generated\PageTable::COLUMN_SLUG,
            'content' => Table\Generated\PageTable::COLUMN_CONTENT,
            'metadata' => $this->fieldJson(Table\Generated\PageTable::COLUMN_METADATA),
            'date' => $this->fieldDateTime(Table\Generated\PageTable::COLUMN_DATE),
        ]);

        return $this->build($definition);
    }
}
