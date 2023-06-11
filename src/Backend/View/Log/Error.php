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

namespace Fusio\Impl\Backend\View\Log;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Error
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Error extends ViewAbstract
{
    public function getCollection(int $categoryId, int $startIndex, int $count, ?string $search = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $condition = Condition::withAnd();
        $condition->equals('log.category_id', $categoryId ?: 1);

        if (!empty($search)) {
            $condition->like('message', '%' . $search . '%');
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(['error.id', 'error.message', 'log.path', 'log.date'])
            ->from('fusio_log_error', 'error')
            ->innerJoin('error', 'fusio_log', 'log', 'error.log_id = log.id')
            ->orderBy('error.id', 'DESC')
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        if ($condition->hasCondition()) {
            $queryBuilder->where($condition->getExpression($this->connection->getDatabasePlatform()));
            $queryBuilder->setParameters($condition->getValues());
        }

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_log_error', 'error')
            ->innerJoin('error', 'fusio_log', 'log', 'error.log_id = log.id');

        if ($condition->hasCondition()) {
            $countBuilder->where($condition->getExpression($this->connection->getDatabasePlatform()));
            $countBuilder->setParameters($condition->getValues());
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger('id'),
                'message' => 'message',
                'path' => 'path',
                'date' => $builder->fieldDateTime('date'),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Log\Error::class), 'find'], [$id], [
            'id' => Table\Generated\LogErrorTable::COLUMN_ID,
            'logId' => Table\Generated\LogErrorTable::COLUMN_LOG_ID,
            'message' => Table\Generated\LogErrorTable::COLUMN_MESSAGE,
            'trace' => Table\Generated\LogErrorTable::COLUMN_TRACE,
            'file' => Table\Generated\LogErrorTable::COLUMN_FILE,
            'line' => Table\Generated\LogErrorTable::COLUMN_LINE,
        ]);

        return $builder->build($definition);
    }
}
