<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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
use Fusio\Impl\Table\Routes as TableRoutes;
use PSX\Framework\Loader\Context;
use PSX\Framework\Loader\LocationFinderInterface;
use PSX\Framework\Loader\PathMatcher;
use PSX\Http\Exception as StatusCode;
use PSX\Http\RequestInterface;

/**
 * RoutingParser
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
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
				 WHERE status = :status
				   AND methods LIKE :method';

        // @TODO we could add a priority column to the routes table so that
        // often visited routes are at the top. For this we need to have a cron
        // to set the priority depending on the entries in the log table

        $method      = $request->getMethod();
        $pathMatcher = new PathMatcher($request->getUri()->getPath());
        $result      = $this->connection->fetchAll($sql, array(
            'status' => TableRoutes::STATUS_ACTIVE,
            'method' => '%' . $method . '%'
        ));

        foreach ($result as $row) {
            $parameters = array();

            if (in_array($method, explode('|', $row['methods'])) &&
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
