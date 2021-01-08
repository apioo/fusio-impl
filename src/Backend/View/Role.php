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

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Role
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Role extends ViewAbstract
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
            $sortBy = 'name';
        }

        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_ASC;
        }

        $condition = new Condition();
        $condition->in('status', [Table\Role::STATUS_ACTIVE]);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Role::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Role::class), 'getAll'], [$startIndex, $count, $sortBy, $sortOrder, $condition], [
                'id' => $this->fieldInteger('id'),
                'categoryId' => $this->fieldInteger('category_id'),
                'status' => $this->fieldInteger('status'),
                'name' => 'name',
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Role::class), 'get'], [$this->resolveId($id)], [
            'id' => $this->fieldInteger('id'),
            'categoryId' => $this->fieldInteger('category_id'),
            'status' => $this->fieldInteger('status'),
            'name' => 'name',
            'scopes' => $this->doColumn([$this->getTable(Table\Role\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
        ]);

        return $this->build($definition);
    }

    private function resolveId($id): int
    {
        if (substr($id, 0, 1) === '~') {
            $row = $this->getTable(Table\Role::class)->getOneByName(urldecode(substr($id, 1)));
            return $row['id'] ?? 0;
        } else {
            return (int) $id;
        }
    }
}
