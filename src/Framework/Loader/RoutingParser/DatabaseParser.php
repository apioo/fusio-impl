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
use Fusio\Impl\Framework\Api\Scanner\CategoryFilter;
use Fusio\Impl\Framework\Api\Scanner\FilterFactory;
use Fusio\Impl\Framework\Api\Scanner\CategoriesFilter;
use Fusio\Impl\Table\Operation as TableOperation;
use PSX\Api\Scanner\FilterInterface;
use PSX\Framework\Loader\RoutingCollection;
use PSX\Framework\Loader\RoutingParser\InvalidateableInterface;
use PSX\Framework\Loader\RoutingParserInterface;

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

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getCollection(?FilterInterface $filter = null): RoutingCollection
    {
        $sql = 'SELECT operation.id,
                       operation.http_method,
                       operation.http_path
                  FROM fusio_operation operation
                 WHERE operation.status = :status';

        $params = ['status' => TableOperation::STATUS_ACTIVE];

        if ($filter instanceof CategoryFilter) {
            $sql.= ' AND category_id = :category_id';
            $params['category_id'] = $filter->getId();
        } elseif ($filter instanceof CategoriesFilter) {
            $sql.= ' AND category_id IN (' . implode(', ', array_fill(0, count($filter->getIds()), '?')) . ')';
            $params = array_merge($params, $filter->getIds());
        }

        $sql.= ' ORDER BY operation.id DESC';

        $collection = new RoutingCollection();
        $result = $this->connection->fetchAllAssociative($sql, $params);

        foreach ($result as $row) {
            $controller = 'operation://' . $row['id'];
            $method = $row['id'];

            if ($row['http_method'] === 'GET') {
                $methods = ['OPTIONS', 'HEAD', $row['http_method']];
            } else {
                $methods = ['OPTIONS', $row['http_method']];
            }

            $collection->add($methods, $row['http_path'], [$controller, $method]);
        }

        return $collection;
    }
}
