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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
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
            $sortBy = Table\Generated\ActionTable::COLUMN_ID;
        }
        
        if ($sortOrder === null) {
            $sortOrder = OrderBy::DESC;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ActionTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);
        $condition->equals(Table\Generated\ActionTable::COLUMN_STATUS, Table\Action::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\ActionTable::COLUMN_NAME, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Action::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Action::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\ActionTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\ActionTable::COLUMN_STATUS),
                'name' => Table\Generated\ActionTable::COLUMN_NAME,
                'metadata' => $builder->fieldJson(Table\Generated\ActionTable::COLUMN_METADATA),
                'date' => $builder->fieldDateTime(Table\Generated\ActionTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Action::class), 'findOneByIdentifier'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\ActionTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\ActionTable::COLUMN_STATUS),
            'name' => Table\Generated\ActionTable::COLUMN_NAME,
            'class' => Table\Generated\ActionTable::COLUMN_CLASS,
            'async' => Table\Generated\ActionTable::COLUMN_ASYNC,
            'engine' => Table\Generated\ActionTable::COLUMN_ENGINE,
            'config' => $builder->fieldCallback(Table\Generated\ActionTable::COLUMN_CONFIG, function ($config) {
                return Service\Action::unserializeConfig($config);
            }),
            'metadata' => $builder->fieldJson(Table\Generated\ActionTable::COLUMN_METADATA),
            'date' => $builder->fieldDateTime(Table\Generated\ActionTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
