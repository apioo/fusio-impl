<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Adapter;

/**
 * TransformAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class TransformAbstract implements TransformInterface
{
    /**
     * @var array
     */
    private $routes;

    /**
     * @var array
     */
    private $action;

    /**
     * @var array
     */
    private $schema;

    public function transform($data)
    {
        $this->doParse($data);

        return [
            'routes' => $this->routes,
            'action' => $this->action,
            'schema' => $this->schema,
        ];
    }
    
    protected function addRoute(array $route)
    {
        $this->routes[] = $route;
    }
    
    protected function addAction(array $action)
    {
        $this->action[] = $action;
    }
    
    protected function addSchema(array $schema)
    {
        $this->schema[] = $schema;
    }

    abstract protected function doParse($data);
}
