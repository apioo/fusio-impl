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

namespace Fusio\Impl\Service\Agent\Intent;

use Fusio\Impl\Service\Agent\IntentInterface;

/**
 * ActionIntent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ActionIntent implements IntentInterface
{
    public function getMessage(): string
    {
        $hint = 'The user has the intent to develop a new action.' . "\n";
        $hint.= 'Therefor you need to transform the provided business logic by the user message into PHP code.' . "\n";
        $hint.= "\n";
        $hint.= 'The resulting PHP code must be wrapped into the following code:' . "\n";
        $hint.= "\n";
        $hint.= '--' . "\n";
        $hint.= <<<PHP
<?php

use Doctrine\DBAL\Connection;
use Fusio\Worker\ExecuteContext;
use Fusio\Worker\ExecuteRequest;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Response\FactoryInterface;
use Fusio\Engine\DispatcherInterface;
use Psr\Log\LoggerInterface;

return function(ExecuteRequest \$request, ExecuteContext \$context, ConnectorInterface \$connector, FactoryInterface \$response, DispatcherInterface \$dispatcher, LoggerInterface \$logger) {

// [INSERT_CODE_HERE]

};

PHP;
        $hint.= '--' . "\n";
        $hint.= "\n";
        $hint.= 'Replace the line "// [INSERT_CODE_HERE]" with the code which you have generated.' . "\n";
        $hint.= "\n";
        $hint.= 'If the business logic wants to interact with an external service i.e. a database or remote HTTP endpoint, then you can use the getConnection method at the connector argument to access those external services.' . "\n";
        $hint.= 'You can get a list of all available connections through the "backend_connection_getAll" tool.' . "\n";
        $hint.= "\n";
        $hint.= 'If the connection has as class "Fusio.Impl.Connection.System" or "Fusio.Adapter.Sql.Connection.Sql" it is a Doctrine DBAL connection, this means you can use all methods of the Doctrine DBAL library.' . "\n";
        $hint.= 'If the connection has as class "Fusio.Adapter.Http.Connection.Http" it is a Guzzle connection, this means you can use all methods of the Guzzle HTTP client library.' . "\n";
        $hint.= "\n";
        $hint.= 'If the business logic needs to work with a database table you can get all available tables for a specific connection through the "backend_database_getTables" tool where you need to provide a connection id.' . "\n";
        $hint.= 'If you need to get a concrete table schema you can use the "backend_database_getTable" tool where you need to provide the connection id and table name.' . "\n";
        $hint.= 'If you need to get data from the incoming HTTP request you can get query and uri parameters through the "$request->getArguments()->get(\'[name]\')" method and the body with "$request->getPayload()".' . "\n";
        $hint.= 'To add logging you can use the "$logger" argument which is a PSR-3 compatible logging interface.' . "\n";
        $hint.= 'To dispatch an event you can use the "$dispatcher" argument which has a method "dispatch" where the first argument is the event name and the second the payload.' . "\n";
        $hint.= "\n";
        $hint.= 'The generated business logic must use the build method of the "$response" factory to return a result.' . "\n";
        $hint.= "\n";

        return $hint;
    }

    public function getTools(): array
    {
        return [
            'backend_action_getAll',
            'backend_action_get',
            'backend_action_getClasses',
            'backend_action_getForm',
            'backend_action_execute',
            'backend_action_get',
            'backend_action_update',
            'backend_action_delete',
            'backend_connection_getAll',
            'backend_connection_get',
            'backend_connection_database_getTables',
            'backend_connection_database_getTable',
            'backend_connection_filesystem_getAll',
            'backend_connection_filesystem_get',
            'backend_connection_http_execute',
            'backend_connection_sdk_get',
        ];
    }

    public function getResponseFormat(): ?array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'Action',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'description' => 'A short and precise name as lower case and separated by hyphens which summarizes the business logic of the user message',
                            'type' => 'string',
                        ],
                        'class' => [
                            'type' => 'string',
                            'enum' => ['Fusio.Adapter.Worker.Action.WorkerPHPLocal'],
                            'default' => 'Fusio.Adapter.Worker.Action.WorkerPHPLocal',
                        ],
                        'config' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => [
                                    'description' => 'Then generated PHP code',
                                    'type' => 'string',
                                ],
                            ],
                            'required' => ['code'],
                            'additionalProperties' => false,
                        ],
                    ],
                    'required' => ['name', 'class', 'config'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
