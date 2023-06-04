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

namespace Fusio\Impl\Framework\Loader\RoutingParser;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Framework\Api\Scanner\Filter;
use Fusio\Impl\Table\Operation as TableOperation;
use PSX\Api\Scanner\FilterInterface;
use PSX\Framework\Loader\RoutingCollection;
use PSX\Framework\Loader\RoutingParser\InvalidateableInterface;
use PSX\Framework\Loader\RoutingParserInterface;

/**
 * DatabaseParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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

        if ($filter instanceof Filter) {
            $sql.= ' AND category_id = :category_id';
            $params['category_id'] = $filter->getId();
        }

        $sql.= ' ORDER BY operation.id DESC';

        $collection = new RoutingCollection();
        $result = $this->connection->fetchAllAssociative($sql, $params);

        foreach ($result as $row) {
            $controller = 'operation:' . $row['id'];
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
