<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

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
        $params = [
            'status' => TableRoutes::STATUS_ACTIVE,
        ];

        // check whether we have a known system path
        foreach ($paths as $systemPath) {
            if (strpos($path, '/' . $systemPath) === 0) {
                $found = true;
                $sql  .= 'AND path LIKE :path';
                $params['path'] = '/' . $systemPath . '%';
                break;
            }
        }

        // if not we only want to search the user routes and exclude all system
        // paths
        if (!$found) {
            foreach ($paths as $index => $systemPath) {
                $key = 'path_' . $index;
                $sql.= 'AND path NOT LIKE :' . $key . ' ';
                $params[$key] = '/' . $systemPath . '%';
            }
        }

        $method      = $request->getMethod();
        $pathMatcher = new PathMatcher($path);
        $result      = $this->connection->fetchAll($sql, $params);

        foreach ($result as $row) {
            $parameters = array();

            if (($row['methods'] == 'ANY' || in_array($method, explode('|', $row['methods']))) &&
                $pathMatcher->match($row['path'], $parameters)) {
                $context->set(Context::KEY_FRAGMENT, $parameters);
                $context->set(Context::KEY_PATH, $row['path']);
                $context->set(Context::KEY_SOURCE, $row['controller']);
                $context->set('fusio.routeId', $row['id']);

                return $request;
            }
        }

        return null;
    }
}
