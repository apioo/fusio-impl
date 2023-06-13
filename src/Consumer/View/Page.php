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

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Page
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
