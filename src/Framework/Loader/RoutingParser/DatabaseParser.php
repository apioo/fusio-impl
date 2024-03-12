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

namespace Fusio\Impl\Framework\Loader\RoutingParser;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Framework\Api\Scanner\CategoriesFilter;
use Fusio\Impl\Framework\Api\Scanner\CategoryFilter;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Operation as TableOperation;
use PSX\Api\Scanner\FilterInterface;
use PSX\Framework\Loader\RoutingCollection;
use PSX\Framework\Loader\RoutingParserInterface;
use PSX\Sql\Condition;

/**
 * DatabaseParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class DatabaseParser implements RoutingParserInterface
{
    private Connection $connection;
    private FrameworkConfig $frameworkConfig;

    public function __construct(Connection $connection, FrameworkConfig $frameworkConfig)
    {
        $this->connection = $connection;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function getCollection(?FilterInterface $filter = null): RoutingCollection
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, TableOperation::STATUS_ACTIVE);

        if ($filter instanceof CategoryFilter) {
            $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $filter->getId());
        } elseif ($filter instanceof CategoriesFilter) {
            $condition->in(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $filter->getIds());
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\OperationTable::COLUMN_ID,
                Table\Generated\OperationTable::COLUMN_HTTP_METHOD,
                Table\Generated\OperationTable::COLUMN_HTTP_PATH,
            ])
            ->from('fusio_operation', 'operation')
            ->orderBy(Table\Generated\OperationTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $collection = new RoutingCollection();
        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        foreach ($result as $row) {
            $controller = 'operation://' . $row[Table\Generated\OperationTable::COLUMN_ID];
            $method = $row[Table\Generated\OperationTable::COLUMN_ID];

            if ($row[Table\Generated\OperationTable::COLUMN_HTTP_METHOD] === 'GET') {
                $methods = ['OPTIONS', 'HEAD', $row[Table\Generated\OperationTable::COLUMN_HTTP_METHOD]];
            } else {
                $methods = ['OPTIONS', $row[Table\Generated\OperationTable::COLUMN_HTTP_METHOD]];
            }

            $collection->add($methods, $row[Table\Generated\OperationTable::COLUMN_HTTP_PATH], [$controller, $method]);
        }

        return $collection;
    }
}
