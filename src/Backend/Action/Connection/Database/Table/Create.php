<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Connection\Database\Table;

use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Action\Connection\Database\TableAbstract;
use Fusio\Model\Backend\DatabaseTable;
use PSX\Http\Environment\HttpResponse;

/**
 * Create
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Create extends TableAbstract
{
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $this->assertConnectionEnabled();

        $connection = $this->getConnection($request);
        $schemaManager = $connection->createSchemaManager();

        $body = $request->getPayload();

        assert($body instanceof DatabaseTable);

        $schemaManager->createTable($this->createTable($body));

        return new HttpResponse(201, [], [
            'success' => true,
            'message' => 'Table successfully created',
        ]);
    }
}
