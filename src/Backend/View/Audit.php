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

use Fusio\Impl\Backend\Filter\Audit\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Audit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Audit extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, QueryFilter $filter)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $sortBy = Table\Generated\AuditTable::COLUMN_ID;

        $condition = $filter->getCondition();
        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Audit::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Audit::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, OrderBy::DESC], [
                'id' => $builder->fieldInteger(Table\Generated\AuditTable::COLUMN_ID),
                'event' => Table\Generated\AuditTable::COLUMN_EVENT,
                'ip' => Table\Generated\AuditTable::COLUMN_IP,
                'message' => Table\Generated\AuditTable::COLUMN_MESSAGE,
                'date' => $builder->fieldDateTime(Table\Generated\AuditTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Audit::class), 'find'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\AuditTable::COLUMN_ID),
            'app' => $builder->doEntity([$this->getTable(Table\App::class), 'find'], [new Reference('app_id')], [
                'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
            ]),
            'user' => $builder->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                'id' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\UserTable::COLUMN_STATUS),
                'name' => Table\Generated\UserTable::COLUMN_NAME,
            ]),
            'refId' => Table\Generated\AuditTable::COLUMN_REF_ID,
            'event' => Table\Generated\AuditTable::COLUMN_EVENT,
            'ip' => Table\Generated\AuditTable::COLUMN_IP,
            'message' => Table\Generated\AuditTable::COLUMN_MESSAGE,
            'content' => $builder->fieldJson(Table\Generated\AuditTable::COLUMN_CONTENT),
            'date' => $builder->fieldDateTime(Table\Generated\AuditTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
