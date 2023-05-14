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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
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
    public function getCollection(int $startIndex = 0)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 32;
        $sortBy = Table\Generated\PageTable::COLUMN_SLUG;

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\PageTable::COLUMN_STATUS, Table\Page::STATUS_VISIBLE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Page::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Page::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, OrderBy::ASC], [
                'id' => $builder->fieldInteger(Table\Generated\PageTable::COLUMN_ID),
                'title' => Table\Generated\PageTable::COLUMN_TITLE,
                'slug' => Table\Generated\PageTable::COLUMN_SLUG,
                'date' => $builder->fieldDateTime(Table\Generated\PageTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $pageId)
    {
        if (str_starts_with($pageId, '~')) {
            $method = 'findOneBySlug';
            $pageId = urldecode(substr($pageId, 1));
        } else {
            $method = 'find';
            $pageId = (int) $pageId;
        }

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Page::class), $method], [$pageId], [
            'id' => $builder->fieldInteger(Table\Generated\PageTable::COLUMN_ID),
            'title' => Table\Generated\PageTable::COLUMN_TITLE,
            'slug' => Table\Generated\PageTable::COLUMN_SLUG,
            'content' => Table\Generated\PageTable::COLUMN_CONTENT,
            'date' => $builder->fieldDateTime(Table\Generated\PageTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
