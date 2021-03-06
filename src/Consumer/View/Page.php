<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Sql\Condition;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Page
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Page extends ViewAbstract
{
    public function getCollection(int $startIndex = 0)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 32;

        $condition = new Condition();
        $condition->equals('status', Table\Page::STATUS_VISIBLE);

        $definition = [
            'totalResults' => $this->getTable(Table\Page::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Page::class), 'getAll'], [$startIndex, $count, 'slug', 'ASC', $condition], [
                'id' => $this->fieldInteger('id'),
                'title' => 'title',
                'slug' => 'slug',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($pageId)
    {
        $definition = $this->doEntity([$this->getTable(Table\Page::class), 'get'], [$this->resolveId($pageId)], [
            'id' => $this->fieldInteger('id'),
            'title' => 'title',
            'slug' => 'slug',
            'content' => 'content',
            'date' => $this->fieldDateTime('date'),
        ]);

        return $this->build($definition);
    }

    private function resolveId($id): int
    {
        if (substr($id, 0, 1) === '~') {
            $row = $this->getTable(Table\Page::class)->getOneBySlug(urldecode(substr($id, 1)));
            return $row['id'] ?? 0;
        } else {
            return (int) $id;
        }
    }
}
