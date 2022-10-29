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

namespace Fusio\Impl\Service\Route;

use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Method
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Method
{
    private Table\Route\Method $methodTable;
    private Table\Route\Response $responseTable;
    private Table\Scope\Route $scopeTable;
    private Loader $schemaLoader;

    public function __construct(Table\Route\Method $methodTable, Table\Route\Response $responseTable, Table\Scope\Route $scopeTable, Loader $schemaLoader)
    {
        $this->methodTable   = $methodTable;
        $this->responseTable = $responseTable;
        $this->scopeTable    = $scopeTable;
        $this->schemaLoader  = $schemaLoader;
    }

    /**
     * Returns the method configuration for the provide route, version and request method
     */
    public function getMethod(int $routeId, ?string $version, string $method): array|false
    {
        if ($version === '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = $this->methodTable->getVersion($routeId, (int) $version);
        }

        if (empty($version)) {
            throw new StatusCode\UnsupportedMediaTypeException('Version does not exist');
        }

        return $this->methodTable->getMethod($routeId, $version, $method);
    }

    public function getAllowedMethods(int $routeId, ?string $version): array
    {
        if ($version === '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = (int) $version;
        }

        return $this->methodTable->getAllowedMethods($routeId, $version);
    }

    public function getRequestSchemas(int $routeId, ?string $version): array
    {
        if ($version === '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        } else {
            $version = $this->methodTable->getVersion($routeId, (int) $version);
        }

        if (empty($version)) {
            throw new StatusCode\UnsupportedMediaTypeException('Version does not exist');
        }

        $methods = $this->methodTable->getMethods($routeId, $version);
        $schemas = [];

        foreach ($methods as $method) {
            $schemaId = $method['request'];
            if (!empty($schemaId)) {
                $schemas[$method['method']] = $schemaId;
            }
        }

        return $schemas;
    }
}
