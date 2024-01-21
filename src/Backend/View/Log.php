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

use Fusio\Impl\Backend\Filter\Log\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Log
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Log extends ViewAbstract
{
    public function getCollection(int $categoryId, int $startIndex, int $count, QueryFilter $filter, ?string $tenantId = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $sortBy = Table\Generated\LogTable::COLUMN_ID;

        $condition = $filter->getCondition();
        $condition->equals(Table\Generated\LogTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Table\Generated\LogTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Log::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Log::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, OrderBy::DESC], [
                'id' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_ID),
                'appId' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_APP_ID),
                'operationId' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_OPERATION_ID),
                'ip' => Table\Generated\LogTable::COLUMN_IP,
                'userAgent' => Table\Generated\LogTable::COLUMN_USER_AGENT,
                'method' => Table\Generated\LogTable::COLUMN_METHOD,
                'path' => Table\Generated\LogTable::COLUMN_PATH,
                'date' => $builder->fieldDateTime(Table\Generated\LogTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id, ?string $tenantId = null)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Log::class), 'findOneByIdentifier'], [$id, $tenantId], [
            'id' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_ID),
            'appId' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_APP_ID),
            'operationId' => $builder->fieldInteger(Table\Generated\LogTable::COLUMN_OPERATION_ID),
            'ip' => Table\Generated\LogTable::COLUMN_IP,
            'userAgent' => Table\Generated\LogTable::COLUMN_USER_AGENT,
            'method' => Table\Generated\LogTable::COLUMN_METHOD,
            'path' => Table\Generated\LogTable::COLUMN_PATH,
            'header' => Table\Generated\LogTable::COLUMN_HEADER,
            'body' => Table\Generated\LogTable::COLUMN_BODY,
            'errors' => $builder->doCollection([$this->getTable(Table\Log\Error::class), 'findByLogId'], [new Reference('id')], [
                'message' => Table\Generated\LogErrorTable::COLUMN_MESSAGE,
                'trace' => Table\Generated\LogErrorTable::COLUMN_TRACE,
                'file' => Table\Generated\LogErrorTable::COLUMN_FILE,
                'line' => Table\Generated\LogErrorTable::COLUMN_LINE,
            ]),
            'date' => $builder->fieldDateTime(Table\Generated\LogTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
