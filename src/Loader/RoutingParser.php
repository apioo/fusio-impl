<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Loader;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Backend\Filter\Routes\Path;
use Fusio\Impl\Table\Routes as TableRoutes;
use PSX\Framework\Loader\Context;
use PSX\Framework\Loader\LocationFinderInterface;
use PSX\Framework\Loader\PathMatcher;
use PSX\Http\RequestInterface;

/**
 * RoutingParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RoutingParser implements LocationFinderInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param \PSX\Http\RequestInterface $request
     * @param \Fusio\Impl\Loader\Context $context
     * @return \PSX\Http\RequestInterface|null
     */
    public function resolve(RequestInterface $request, Context $context)
    {
        $sql = 'SELECT id,
                       methods,
                       path,
                       controller
                  FROM fusio_routes
                 WHERE status = :status ';

        $paths  = Path::getReserved();
        $found  = false;
        $path   = $request->getUri()->getPath();
        $params = ['status' => TableRoutes::STATUS_ACTIVE];

        // check whether we have a known system path if yes select only those
        // routes matching the specific priority
        foreach ($paths as $value => $systemPath) {
            if (strpos($path, '/' . $systemPath) === 0) {
                $found = true;
                $sql  .= 'AND (priority >= ' . $value . ' AND priority < ' . ($value << 1) . ') ';
                break;
            }
        }

        // if not we only want to search the user routes and exclude all system
        // paths means all priorities under 0x1000000
        if (!$found) {
            $sql.= 'AND (priority IS NULL OR priority < 0x1000000) ';
        }

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
                $context->setRouteId($row['id']);

                return $request;
            }
        }

        return null;
    }
}
