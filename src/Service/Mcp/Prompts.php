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
            $actionLogic = $arguments?->logic ?? '';

            $text = 'Create a new backend action and use as class "Fusio.Adapter.Worker.Action.WorkerPHPLocal".';
            $text.= 'Add a key "code" to the config property and for the value you need to transform the following logic into PHP code:';
            $text.= "\n";
            $text.= '--' . "\n";
            $text.= $actionLogic;
            $text.= "\n";
            $text.= '--' . "\n";
            $text.= 'As name of the action summarize the logic above in lower case and seperated by hyphens.';
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
            $text.= '';
            $text.= 'If the connection has as class "Fusio.Impl.Connection.System" or "Fusio.Adapter.Sql.Connection.Sql" it is a Doctrine DBAL connection, this means you can use all methods of the Doctrine DBAL library.';
            $text.= 'If the connection has as class "Fusio.Adapter.Http.Connection.Http" it is a Guzzle connection, this means you can use all methods of the Guzzle HTTP client library.';
            $text.= '';
            $text.= 'If the business logic needs to work with a database table you can get all available tables for a specific connection through the "backend-database-getTables" tool where you need to provide a connection id.';
            $text.= 'If you need to get a concrete table schema you can use the "backend-database-getTable" tool where you need to provide the connection id and table name.';
            $text.= 'If you need to get data from the incoming HTTP request you can get query and uri parameters through the "$request->getArguments()->get(\'[name]\')" method and the body with "$request->getPayload()".';
            $text.= 'To add logging you can use the "$logger" argument which is a PSR-3 compatible logging interface.';
            $text.= 'To dispatch an event you can use the "$dispatcher" argument which has a method "dispatch" where the first argument is the event name and the second the payload.';
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
