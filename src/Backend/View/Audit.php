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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
