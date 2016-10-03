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

namespace Fusio\Impl\Service\Routes;

use Fusio\Engine\Schema\LoaderInterface;
use Fusio\Impl\Schema\LazySchema;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Schema\SchemaInterface;
use PSX\Sql\Condition;

/**
 * Method
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Method
{
    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $methodTable;

    /**
     * @var \Fusio\Engine\Schema\LoaderInterface
     */
    protected $schemaLoader;

    public function __construct(Table\Routes\Method $methodTable, LoaderInterface $schemaLoader)
    {
        $this->methodTable  = $methodTable;
        $this->schemaLoader = $schemaLoader;
    }

    public function getDocumentation($routeId, $version, $path)
    {
        if ($version == '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        }

        $methods  = $this->methodTable->getMethods($routeId, $version, true);
        $resource = new Resource($this->getStatusFromMethods($methods), $path);

        foreach ($methods as $method) {
            $resourceMethod = Resource\Factory::getMethod($method['method']);

            if ($method['status'] == Resource::STATUS_DEVELOPMENT) {
                if (!empty($method['request'])) {
                    $resourceMethod->setRequest(new LazySchema($this->schemaLoader, $method['request']));
                }
            } else {
                if (!empty($method['requestCache'])) {
                    $request = unserialize($method['requestCache']);
                    if ($request instanceof SchemaInterface) {
                        $resourceMethod->setRequest($request);
                    }
                }
            }

            if ($method['status'] == Resource::STATUS_DEVELOPMENT) {
                if (!empty($method['response'])) {
                    $resourceMethod->addResponse(200, new LazySchema($this->schemaLoader, $method['response']));
                }
            } else {
                if (!empty($method['responseCache'])) {
                    $response = unserialize($method['responseCache']);
                    if ($response instanceof SchemaInterface) {
                        $resourceMethod->addResponse(200, $response);
                    }
                }
            }

            $resource->addMethod($resourceMethod);
        }

        return $resource;
    }

    public function getMethod($routeId, $version, $method)
    {
        if ($version == '*' || empty($version)) {
            $version = $this->methodTable->getLatestVersion($routeId);
        }

        $condition = new Condition();
        $condition->equals('routeId', $routeId);
        $condition->equals('version', $version);
        $condition->equals('method', $method);

        return $this->methodTable->getOneBy($condition);
    }

    public function getAllowedMethods($routeId, $version)
    {
        return $this->methodTable->getAllowedMethods($routeId, $version);
    }

    private function getStatusFromMethods(array $methods)
    {
        $method = reset($methods);

        return isset($method['status']) ? $method['status'] : Resource::STATUS_DEVELOPMENT;
    }
}
