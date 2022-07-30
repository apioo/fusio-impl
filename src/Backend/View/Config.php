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
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Config extends ViewAbstract
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
            $sortBy = Table\Generated\ConfigTable::COLUMN_NAME;
        }

        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_ASC;
        }

        $condition = new Condition();

        if (!empty($search)) {
            $condition->like(Table\Generated\ConfigTable::COLUMN_NAME, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Config::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Config::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\ConfigTable::COLUMN_ID),
                'type' => $this->fieldInteger(Table\Generated\ConfigTable::COLUMN_TYPE),
                'name' => Table\Generated\ConfigTable::COLUMN_NAME,
                'description' => Table\Generated\ConfigTable::COLUMN_DESCRIPTION,
                'value' => $this->fieldCallback(Table\Generated\ConfigTable::COLUMN_VALUE, function($value, Table\Generated\ConfigRow $row){
                    return \Fusio\Impl\Service\Config::convertValueToType($value, $row->getType());
                }),
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

        $definition = $this->doEntity([$this->getTable(Table\Config::class), $method], [$id], [
            'id' => $this->fieldInteger(Table\Generated\ConfigTable::COLUMN_ID),
            'type' => $this->fieldInteger(Table\Generated\ConfigTable::COLUMN_TYPE),
            'name' => Table\Generated\ConfigTable::COLUMN_NAME,
            'description' => Table\Generated\ConfigTable::COLUMN_DESCRIPTION,
            'value' => $this->fieldCallback(Table\Generated\ConfigTable::COLUMN_VALUE, function($value, Table\Generated\ConfigRow $row){
                return \Fusio\Impl\Service\Config::convertValueToType($value, $row->getType());
            }),
        ]);

        return $this->build($definition);
    }
}
