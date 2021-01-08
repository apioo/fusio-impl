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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Action extends ViewAbstract
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
        $condition->equals('status', Table\Action::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Action::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Action::class), 'getAll'], [$startIndex, $count, $sortBy, $sortOrder, $condition, Fields::blacklist(['class', 'config'])], [
                'id' => $this->fieldInteger('id'),
                'status' => $this->fieldInteger('status'),
                'name' => 'name',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Action::class), 'get'], [$this->resolveId($id)], [
            'id' => $this->fieldInteger('id'),
            'status' => $this->fieldInteger('status'),
            'name' => 'name',
            'class' => 'class',
            'async' => 'async',
            'engine' => 'engine',
            'config' => $this->fieldCallback('config', function ($config) {
                return Service\Action::unserializeConfig($config);
            }),
            'date' => $this->fieldDateTime('date'),
        ]);

        return $this->build($definition);
    }

    private function resolveId($id): int
    {
        if (substr($id, 0, 1) === '~') {
            $row = $this->getTable(Table\Action::class)->getOneByName(substr($id, 1));
            return $row['id'] ?? 0;
        } else {
            return (int) $id;
        }
    }
}
