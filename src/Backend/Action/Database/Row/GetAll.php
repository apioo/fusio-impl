<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Database\Row;

use Doctrine\DBAL\Query\QueryBuilder;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Action\Database\TableAbstract;

/**
 * GetAll
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetAll extends TableAbstract
{
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $connection = $this->getConnection($request);
        $table = $this->getTable($request, $connection->createSchemaManager());

        $allColumns = array_keys($table->getColumns());
        $primaryKey = $this->getPrimaryKeyColumn($table);

        $qb = $connection->createQueryBuilder();
        $qb->select($this->getColumns($request, $allColumns));
        $qb->from($table->getName());

        $this->addFilter($request, $qb, $allColumns);
        $this->addOrderBy($request, $qb, $primaryKey, $allColumns);
        $this->addLimit($request, $qb);

        $totalCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM ' . $table->getName());
        $result = $connection->fetchAllAssociative($qb->getSQL(), $qb->getParameters());

        return [
            'totalResults' => $totalCount,
            'itemsPerPage' => $qb->getMaxResults(),
            'startIndex' => $qb->getFirstResult(),
            'entry' => $result,
        ];
    }

    private function getColumns(RequestInterface $request, array $allColumns): array
    {
        $columns = $request->get('columns');
        if (empty($columns)) {
            return $allColumns;
        }

        $selected = array_intersect(explode(',', $columns), $allColumns);
        if (empty($selected)) {
            return $allColumns;
        }

        return $selected;
    }

    private function addFilter(RequestInterface $request, QueryBuilder $qb, array $allColumns): void
    {
        $filterBy = $request->get('filterBy');
        $filterOp = $request->get('filterOp');
        $filterValue = $request->get('filterValue');

        if (!empty($filterBy) && !empty($filterOp) && !empty($filterValue) && in_array($filterBy, $allColumns)) {
            switch ($filterOp) {
                case 'contains':
                    $qb->where($filterBy . ' LIKE :filter');
                    $qb->setParameter('filter', '%' . $filterValue . '%');
                    break;

                case 'equals':
                    $qb->where($filterBy . ' = :filter');
                    $qb->setParameter('filter', $filterValue);
                    break;

                case 'startsWith':
                    $qb->where($filterBy . ' LIKE :filter');
                    $qb->setParameter('filter', $filterValue . '%');
                    break;

                case 'present':
                    $qb->where($filterBy . ' IS NOT NULL');
                    break;
            }
        }
    }

    private function addOrderBy(RequestInterface $request, QueryBuilder $qb, ?string $primaryKey, array $allColumns): void
    {
        $sortBy = $request->get('sortBy');
        $sortOrder = $request->get('sortOrder');

        if (!empty($sortBy) && !empty($sortOrder) && in_array($sortBy, $allColumns)) {
            $sortOrder = strtoupper($sortOrder);
            $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'DESC';

            $qb->orderBy($sortBy, $sortOrder);
        } elseif (!empty($primaryKey)) {
            $qb->orderBy($primaryKey, 'DESC');
        }
    }

    private function addLimit(RequestInterface $request, QueryBuilder $qb): void
    {
        $startIndex = (int) $request->get('startIndex');
        $count = (int) $request->get('count');
        $limit = 1024;

        $startIndex = $startIndex < 0 ? 0 : $startIndex;
        $count = $count >= 1 && $count <= $limit ? $count : 16;

        $qb->setFirstResult($startIndex);
        $qb->setMaxResults($count);
    }
}
