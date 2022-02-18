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

namespace Fusio\Impl\Framework\Loader\LocationFinder;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Framework\Loader\Context as FusioContext;
use Fusio\Impl\Table\Category as TableCategory;
use Fusio\Impl\Table\Route as TableRoutes;
use PSX\Framework\Loader\Context;
use PSX\Framework\Loader\LocationFinderInterface;
use PSX\Framework\Loader\PathMatcher;
use PSX\Http\RequestInterface;

/**
 * DatabaseFinder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class DatabaseFinder implements LocationFinderInterface
{
    private Connection $connection;
    private TableCategory $categoryTable;

    public function __construct(Connection $connection, TableCategory $categoryTable)
    {
        $this->connection = $connection;
        $this->categoryTable = $categoryTable;
    }

    public function resolve(RequestInterface $request, Context $context): ?RequestInterface
    {
        $sql = 'SELECT id,
                       category_id,
                       methods,
                       path,
                       controller
                  FROM fusio_routes
                 WHERE status = :status ';

        $path   = $request->getUri()->getPath();
        $params = ['status' => TableRoutes::STATUS_ACTIVE];

        $categoryId = $this->categoryTable->getCategoryIdForPath($path);
        $sql.= 'AND category_id = :category_id ';
        $params['category_id'] = $categoryId;

        $sql.= 'ORDER BY priority DESC';

        $method      = $request->getMethod();
        $pathMatcher = new PathMatcher($path);
        $result      = $this->connection->fetchAll($sql, $params);

        foreach ($result as $row) {
            $parameters = array();

            if (($row['methods'] == 'ANY' || in_array($method, explode('|', $row['methods']))) &&
                $pathMatcher->match($row['path'], $parameters)) {
                $context->setParameters($parameters);
                $context->setPath($row['path']);
                $context->setSource($row['controller']);
                if ($context instanceof FusioContext) {
                    $context->setRouteId($row['id']);
                    $context->setCategoryId($row['category_id']);
                }

                return $request;
            }
        }

        return null;
    }
}
