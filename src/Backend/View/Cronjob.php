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

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
            $sortOrder = OrderBy::DESC;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\CronjobTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);
        $condition->equals(Table\Generated\CronjobTable::COLUMN_STATUS, Table\Cronjob::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\CronjobTable::COLUMN_NAME, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Cronjob::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Cronjob::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\CronjobTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\CronjobTable::COLUMN_STATUS),
                'name' => Table\Generated\CronjobTable::COLUMN_NAME,
                'cron' => Table\Generated\CronjobTable::COLUMN_CRON,
                'executeDate' => $builder->fieldDateTime(Table\Generated\CronjobTable::COLUMN_EXECUTE_DATE),
                'exitCode' => $builder->fieldInteger(Table\Generated\CronjobTable::COLUMN_EXIT_CODE),
                'metadata' => $builder->fieldJson(Table\Generated\CronjobTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Cronjob::class), 'findOneByIdentifier'], [$id], [
            'id' => Table\Generated\CronjobTable::COLUMN_ID,
            'status' => $builder->fieldInteger(Table\Generated\CronjobTable::COLUMN_STATUS),
            'name' => Table\Generated\CronjobTable::COLUMN_NAME,
            'cron' => Table\Generated\CronjobTable::COLUMN_CRON,
            'action' => Table\Generated\CronjobTable::COLUMN_ACTION,
            'executeDate' => $builder->fieldDateTime(Table\Generated\CronjobTable::COLUMN_EXECUTE_DATE),
            'exitCode' => $builder->fieldInteger(Table\Generated\CronjobTable::COLUMN_EXIT_CODE),
            'metadata' => $builder->fieldJson(Table\Generated\CronjobTable::COLUMN_METADATA),
            'errors' => $builder->doCollection([$this->getTable(Table\Cronjob\Error::class), 'findByCronjobId'], [new Reference('id')], [
                'message' => Table\Generated\CronjobErrorTable::COLUMN_MESSAGE,
                'trace' => Table\Generated\CronjobErrorTable::COLUMN_TRACE,
                'file' => Table\Generated\CronjobErrorTable::COLUMN_FILE,
                'line' => Table\Generated\CronjobErrorTable::COLUMN_LINE,
            ]),
        ]);

        return $builder->build($definition);
    }
}
