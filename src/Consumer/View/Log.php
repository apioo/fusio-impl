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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Backend\View\Log\QueryFilter;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Log
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Log extends ViewAbstract
{
    public function getCollection(int $userId, int $startIndex, QueryFilter $filter)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        $count = 16;
        $sortBy = Table\Generated\LogTable::COLUMN_ID;

        $condition = $filter->getCondition();
        $condition->equals(Table\Generated\LogTable::COLUMN_USER_ID, $userId);

        $definition = [
            'totalResults' => $this->getTable(Table\Log::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Log::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, Sql::SORT_DESC], [
                'id' => $this->fieldInteger(Table\Generated\LogTable::COLUMN_ID),
                'appId' => $this->fieldInteger(Table\Generated\LogTable::COLUMN_APP_ID),
                'ip' => Table\Generated\LogTable::COLUMN_IP,
                'userAgent' => Table\Generated\LogTable::COLUMN_USER_AGENT,
                'method' => Table\Generated\LogTable::COLUMN_METHOD,
                'path' => Table\Generated\LogTable::COLUMN_PATH,
                'date' => $this->fieldDateTime(Table\Generated\LogTable::COLUMN_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(int $userId, int $logId)
    {
        $condition = new Condition();
        $condition->equals(Table\Generated\LogTable::COLUMN_ID, $logId);
        $condition->equals(Table\Generated\LogTable::COLUMN_USER_ID, $userId);

        $definition = $this->doEntity([$this->getTable(Table\Log::class), 'findOneBy'], [$condition], [
            'id' => $this->fieldInteger(Table\Generated\LogTable::COLUMN_ID),
            'appId' => $this->fieldInteger(Table\Generated\LogTable::COLUMN_APP_ID),
            'ip' => Table\Generated\LogTable::COLUMN_IP,
            'userAgent' => Table\Generated\LogTable::COLUMN_USER_AGENT,
            'method' => Table\Generated\LogTable::COLUMN_METHOD,
            'path' => Table\Generated\LogTable::COLUMN_PATH,
            'header' => Table\Generated\LogTable::COLUMN_HEADER,
            'body' => Table\Generated\LogTable::COLUMN_BODY,
            'date' => $this->fieldDateTime(Table\Generated\LogTable::COLUMN_DATE),
        ]);

        return $this->build($definition);
    }
}
