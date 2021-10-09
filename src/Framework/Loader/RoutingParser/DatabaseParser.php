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

namespace Fusio\Impl\Framework\Loader\RoutingParser;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Framework\Filter\Filter;
use Fusio\Impl\Framework\Loader\RoutingCollection;
use Fusio\Impl\Table\Route as TableRoutes;
use PSX\Api\Listing\FilterInterface;
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
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \PSX\Framework\Loader\RoutingCollection
     */
    private $collection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getCollection(?FilterInterface $filter = null)
    {
        $key = $filter !== null ? $filter->getId() : '0';

        if (isset($this->collection[$key])) {
            return $this->collection[$key];
        }

        $sql = 'SELECT routes.id,
                       routes.category_id,
                       routes.methods,
                       routes.path,
                       routes.controller
                  FROM fusio_routes routes
                 WHERE routes.status = :status';

        $params = ['status' => TableRoutes::STATUS_ACTIVE];

        if ($filter instanceof Filter) {
            $sql.= ' AND category_id = :category_id';
            $params['category_id'] = $filter->getId();
        }

        $sql.= ' ORDER BY priority DESC';

        $collection = new RoutingCollection();
        $result     = $this->connection->fetchAll($sql, $params);

        foreach ($result as $row) {
            $collection->add(explode('|', $row['methods']), $row['path'], $row['controller'], $row['id'], $row['category_id']);
        }

        return $this->collection[$key] = $collection;
    }

    public function clear()
    {
        $this->collection = null;
    }
}
