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

        $condition = $filter->getCondition();
        $condition->equals('user_id', $userId);

        $definition = [
            'totalResults' => $this->getTable(Table\Log::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Log::class), 'findAll'], [$condition, $startIndex, $count, 'id', Sql::SORT_DESC, Fields::blacklist(['header', 'body'])], [
                'id' => $this->fieldInteger('id'),
                'appId' => $this->fieldInteger('app_id'),
                'ip' => 'ip',
                'userAgent' => 'user_agent',
                'method' => 'method',
                'path' => 'path',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(int $userId, int $logId)
    {
        $condition = new Condition();
        $condition->equals('id', $logId);
        $condition->equals('user_id', $userId);

        $definition = $this->doEntity([$this->getTable(Table\Log::class), 'findOneBy'], [$condition], [
            'id' => $this->fieldInteger('id'),
            'appId' => $this->fieldInteger('app_id'),
            'ip' => 'ip',
            'userAgent' => 'user_agent',
            'method' => 'method',
            'path' => 'path',
            'header' => 'header',
            'body' => 'body',
            'date' => $this->fieldDateTime('date'),
        ]);

        return $this->build($definition);
    }
}
