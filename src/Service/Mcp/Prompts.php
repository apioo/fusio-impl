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

namespace Fusio\Impl\Service\Mcp;

use Mcp\Types\GetPromptRequestParams;
use Mcp\Types\GetPromptResult;
use Mcp\Types\ListPromptsResult;
use Mcp\Types\PaginatedRequestParams;
use Mcp\Types\Prompt;
use Mcp\Types\PromptArgument;
use Mcp\Types\PromptMessage;
use Mcp\Types\Role;
use Mcp\Types\TextContent;

/**
 * Prompts
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Prompts
{
    public function list(PaginatedRequestParams $params): ListPromptsResult
    {
        $prompts = [];

        $prompts[] = new Prompt(
            name: 'backend-action-create-prompt',
            description: 'Prompt to create a new backend action with custom logic',
            arguments: [
                new PromptArgument(
                    name: 'name',
                    description: 'Name of the action',
                    required: true
                ),
                new PromptArgument(
                    name: 'logic',
                    description: 'Describe the business logic of the action in simple words i.e. select all data from the table "person" of the system connection and return all rows.',
                    required: true
                )
            ]
        );

        return new ListPromptsResult($prompts);
    }

    public function get(GetPromptRequestParams $params): GetPromptResult
    {
        $name = $params->name;
        $arguments = $params->arguments;

        if ($name === 'backend-action-create-prompt') {
            $actionName = $arguments?->name ?? '';
            $actionLogic = $arguments?->logic ?? '';

            $text = 'Create a new backend action with the tool "backend-action-create" and use as name "' . $actionName . '" and as class "Fusio.Adapter.Worker.Action.WorkerPHPLocal".';
            $text.= 'Add also a config object with a key "code" which contains the following business logic:';
            $text.= "\n";
            $text.= '--' . "\n";
            $text.= $actionLogic;
            $text.= "\n";
            $text.= '--' . "\n";
            $text.= 'You need to transform the described business logic into valid PHP code.';
            $text.= 'The code which you generate is used as an action inside Fusio, which is an open source API management tool, this code helps to implement custom business logic for the user.';
            $text.= 'The resulting PHP code must be wrapped into the following code:';
            $text.= "\n";
            $text.= '--' . "\n";
            $text.= <<<PHP
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
            $text.= '--' . "\n";
            $text.= "\n";
            $text.= 'Replace the line "// [INSERT_CODE_HERE]" with the code which you have generated.';
            $text.= '';
            $text.= 'If the business logic wants to interact with an external service i.e. a database or remote HTTP endpoint, then you can use the getConnection method at the connector argument to access those external services.';
            $text.= 'You can get a list of all available connections through the "backend-connection-getAll" tool.';
            $text.= 'To get more information about a specific connection you can call the "backend-connection-get" tool where you need to provide as "connection_id" the first argument of the getConnection method call and add a "~" as prefix.';
            $text.= '';
            $text.= 'In case the connection has as class "Fusio.Impl.Connection.System" or "Fusio.Adapter.Sql.Connection.Sql" it is a Doctrine DBAL connection, this means you can use all methods of the Doctrine DBAL library.';
            $text.= '';
            $text.= 'If the business logic needs to work with a database table you can get all available tables for a specific connection through the "backend-database-getTables" tool where you need to provide as "connection_id" the first argument of the getConnection method call and add a "~" as prefix.';
            $text.= 'If you need to get a concrete table schema you can use the "backend-database-getTable" tool with the same connection id and the target table name.';
            $text.= '';
            $text.= 'The generated business logic must use the build method of the "$response" factory to return a result.';
            $text.= '';

            return $this->newPromptResult($text);
        }

        throw new \InvalidArgumentException('Could not resolve prompt: ' . $name);
    }

    private function newPromptResult(string $text, ?string $description = null): GetPromptResult
    {
        return new GetPromptResult(
            messages: [
                new PromptMessage(
                    role: Role::USER,
                    content: new TextContent($text)
                )
            ],
            description: $description
        );
    }
}
