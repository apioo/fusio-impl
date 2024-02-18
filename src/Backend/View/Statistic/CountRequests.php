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

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\Log;
use Fusio\Impl\Table;
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
    public function getView(Log\LogQueryFilter $filter, ContextInterface $context): array
    {
        $condition = $filter->getCondition([], 'log');
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals('log.' . Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $expression = $condition->getExpression($this->connection->getDatabasePlatform());

        $sql = 'SELECT COUNT(log.id) AS cnt
                  FROM fusio_log log
                 WHERE ' . $expression;

        $row = $this->connection->fetchAssociative($sql, $condition->getValues());

        return [
            'count' => (int) ($row['cnt'] ?? 0),
            'from'  => $filter->getFrom()->format(\DateTimeInterface::RFC3339),
            'to'    => $filter->getTo()->format(\DateTimeInterface::RFC3339),
        ];
    }
}
