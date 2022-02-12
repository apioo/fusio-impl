<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * Cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Cronjob extends ViewAbstract
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
            $sortBy = Table\Generated\CronjobTable::COLUMN_ID;
        }

        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_DESC;
        }

        $condition = new Condition();
        $condition->equals(Table\Generated\CronjobTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);
        $condition->equals(Table\Generated\CronjobTable::COLUMN_STATUS, Table\Cronjob::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\CronjobTable::COLUMN_NAME, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Cronjob::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Cronjob::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\CronjobTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\CronjobTable::COLUMN_STATUS),
                'name' => Table\Generated\CronjobTable::COLUMN_NAME,
                'cron' => Table\Generated\CronjobTable::COLUMN_CRON,
                'executeDate' => $this->fieldDateTime(Table\Generated\CronjobTable::COLUMN_EXECUTE_DATE),
                'exitCode' => $this->fieldInteger(Table\Generated\CronjobTable::COLUMN_EXIT_CODE),
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

        $definition = $this->doEntity([$this->getTable(Table\Cronjob::class), $method], [$id], [
            'id' => Table\Generated\CronjobTable::COLUMN_ID,
            'status' => $this->fieldInteger(Table\Generated\CronjobTable::COLUMN_STATUS),
            'name' => Table\Generated\CronjobTable::COLUMN_NAME,
            'cron' => Table\Generated\CronjobTable::COLUMN_CRON,
            'action' => Table\Generated\CronjobTable::COLUMN_ACTION,
            'executeDate' => $this->fieldDateTime(Table\Generated\CronjobTable::COLUMN_EXECUTE_DATE),
            'exitCode' => $this->fieldInteger(Table\Generated\CronjobTable::COLUMN_EXIT_CODE),
            'errors' => $this->doCollection([$this->getTable(Table\Cronjob\Error::class), 'findByCronjobId'], [new Reference('id')], [
                'message' => Table\Generated\CronjobErrorTable::COLUMN_MESSAGE,
                'trace' => Table\Generated\CronjobErrorTable::COLUMN_TRACE,
                'file' => Table\Generated\CronjobErrorTable::COLUMN_FILE,
                'line' => Table\Generated\CronjobErrorTable::COLUMN_LINE,
            ]),
        ]);

        return $this->build($definition);
    }
}
