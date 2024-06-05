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

namespace Fusio\Impl\Backend\Action\Database;

use Fusio\Adapter\Sql\Connection\Sql;
use Fusio\Adapter\Sql\Connection\SqlAdvanced;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Repository;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Connection\System;

/**
 * GetConnections
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetConnections implements ActionInterface
{
    private Repository\ConnectionInterface $repository;
    private Factory\ConnectionInterface $factory;

    public function __construct(Repository\ConnectionInterface $repository, Factory\ConnectionInterface $factory)
    {
        $this->repository = $repository;
        $this->factory    = $factory;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $result = [];
        $connections = $this->repository->getAll();
        foreach ($connections as $connection) {
            $instance = $this->factory->factory($connection->getClass());
            if ($instance instanceof Sql || $instance instanceof SqlAdvanced || $instance instanceof System) {
                $result[] = $connection->getName();
            }
        }

        return [
            'connections' => $result,
        ];
    }
}
