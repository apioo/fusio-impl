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

namespace Fusio\Impl\Backend\View\Statistic;

use Fusio\Impl\Backend\Filter\Log;
use PSX\Sql\ViewAbstract;

/**
 * CountRequests
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CountRequests extends ViewAbstract
{
    public function getView(int $categoryId, Log\QueryFilter $filter)
    {
        $condition  = $filter->getCondition('log');
        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = 'SELECT COUNT(log.id) AS cnt
                  FROM fusio_log log
                 WHERE log.category_id = ?
                   AND ' . $expression;

        $row = $this->connection->fetchAssociative($sql, array_merge([$categoryId], $condition->getValues()));

        return [
            'count' => (int) ($row['cnt'] ?? 0),
            'from'  => $filter->getFrom()->format(\DateTimeInterface::RFC3339),
            'to'    => $filter->getTo()->format(\DateTimeInterface::RFC3339),
        ];
    }
}
