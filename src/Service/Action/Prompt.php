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

namespace Fusio\Impl\Service\Action;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Fusio\Adapter\SdkFabric\Connection\OpenAI;
use Fusio\Adapter\Worker\Action\WorkerPHPLocal;
use Fusio\Engine\Connector;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Repository\ConnectionDatabase;
use Fusio\Model\Backend\Action;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionPrompt;
use Fusio\Model\Common\Metadata;
use Generator;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Json\Parser;
use SdkFabric\Openai\Client;
use SdkFabric\Openai\ErrorException;
use SdkFabric\Openai\ResponseRequest;
use SdkFabric\Openai\ResponseRequestInputMessage;
use SdkFabric\Openai\ResponseRequestText;
use SdkFabric\Openai\ResponseRequestTextFormatJsonSchema;
use SdkFabric\Openai\ResponseResponse;
use SdkFabric\Openai\ResponseResponseOutputMessage;
use stdClass;

/**
 * Prompt
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Prompt
{
    public function __construct(private Connector $connector, private ConnectionDatabase $connectionRepository)
    {
    }

    public function prompt(ActionPrompt $prompt, ?string $previousId = null): Action
    {
        if (empty($previousId)) {
            $instructions = $this->generateInstructions();
            $instructions = null;
        } else {
            $instructions = null;
        }

        $input = new ResponseRequestInputMessage();
        $input->setRole('user');
        $input->setContent($prompt->getPrompt());

        return $this->generateCode([$input], $instructions, $previousId);
    }

    private function getOpenAIClient(): Client
    {
        $id = $this->connectionRepository->getFirstIdByClass(ClassName::serialize(OpenAI::class));
        if (empty($id)) {
            throw new InternalServerErrorException('Could not find OpenAI connection');
        }

        $client = $this->connector->getConnection($id);
        if (!$client instanceof Client) {
            throw new InternalServerErrorException('Could not find OpenAI connection');
        }

        return $client;
    }

    private function generateCode(array $inputs, ?string $instructions, ?string $previousId): Action
    {
        $client = $this->getOpenAIClient();

        $request = new ResponseRequest();
        $request->setModel('gpt-5-mini');
        $request->setInput($inputs);

        if (!empty($instructions)) {
            $request->setInstructions($instructions);
        }

        if (!empty($previousId)) {
            $request->setPreviousResponseId($previousId);
        }

        $request->setText($this->getFormat());

        try {
            $response = $client->responses()->create($request);
        } catch (ErrorException $e) {
            $message = $e->getPayload()?->getError()?->getMessage();
            if (!empty($message)) {
                throw new InternalServerErrorException('Could not get response: ' . $message);
            } else {
                throw new InternalServerErrorException('Could not get response');
            }
        }

        $content = $this->getFirstContent($response);
        if (empty($content)) {
            throw new InternalServerErrorException('Could not get response');
        }

        return $this->buildAction($content, $response);
    }

    private function generateInstructions(): string
    {
        $text = 'You need to transform the logic provided by the user into PHP code which is used inside an action at Fusio, an open source API management tool.' . "\n";
        $text.= 'The resulting PHP code must be wrapped into the following code:' . "\n";
        $text.= "\n";
        $text.= '<code>' . "\n";
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
        $text.= '</code>' . "\n";
        $text.= "\n";
        $text.= 'Replace the line "// [INSERT_CODE_HERE]" with the code which you have generated.' . "\n";
        $text.=  "\n";
        $text.= 'If the described logic wants to interact with an external service i.e. a database or remote HTTP endpoint you need to use the getConnection method on the $connector.' . "\n";
        $text.= 'At first argument you can use one of the following connections to access those external services:' . "\n";

        foreach ($this->getAllConnections() as $connection) {
            $text.= '* ' . $connection . "\n";
        }

        $text.= "\n";
        $text.= 'If you need to get data from the incoming HTTP request you can get query and uri parameters through the "$request->getArguments()->get(\'[name]\')" method and the body with "$request->getPayload()".' . "\n";
        $text.= 'To add logging you can use the "$logger" argument which is a PSR-3 compatible logging interface.' . "\n";
        $text.= 'To dispatch an event you can use the "$dispatcher" argument which has a method "dispatch" where the first argument is the event name and the second the payload.' . "\n";
        $text.=  "\n";
        $text.= 'The generated business logic must use the build method of the "$response" factory to return a result.' . "\n";
        $text.=  "\n";
        $text.= 'If needed the following list shows all available database tables and columns which help to generate SQL queries:' . "\n";

        foreach ($this->getAllTables() as $line) {
            $text.= $line . "\n";
        }

        return $text;
    }

    private function getAllConnections(): Generator
    {
        $connections = $this->connectionRepository->getAll();
        foreach ($connections as $connection) {
            $description = null;
            if (in_array($connection->getClass(), ['Fusio.Impl.Connection.System', 'Fusio.Adapter.Sql.Connection.Sql', 'Fusio.Adapter.Sql.Connection.SqlAdvanced'])) {
                $description = 'Doctrine DBAL Connection';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Amqp.Connection.Amqp') {
                $description = 'php-amqplib Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Beanstalk.Connection.Beanstalk') {
                $description = 'Pheanstalk Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Elasticsearch.Connection.Elasticsearch') {
                $description = 'Elasticsearch Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.File.Connection.Filesystem') {
                $description = 'Flysystem';
            } elseif ($connection->getClass() === 'Fusio.Adapter.GraphQL.Connection.GraphQL') {
                $description = 'GraphQL Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Http.Connection.Http') {
                $description = 'Guzzle HTTP Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Memcache.Connection.Memcache') {
                $description = 'PHP Memcache Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Mongodb.Connection.MongoDB') {
                $description = 'Mongodb Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Redis.Connection.Redis') {
                $description = 'PhpRedis Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Smtp.Connection.Smtp') {
                $description = 'Symfony Mailer';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Soap.Connection.Soap') {
                $description = 'PHP SOAP Client';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Stripe.Connection.Stripe') {
                $description = 'Stripe SDK';
            } elseif ($connection->getClass() === 'Fusio.Adapter.Worker.Connection.Worker') {
                $description = 'Fusio Worker';
            }

            if ($description !== null) {
                yield $connection->getName() . ': ' . $description;
            } else {
                yield $connection->getName();
            }
        }
    }

    private function getAllTables(): Generator
    {
        $connections = $this->connectionRepository->getAll();
        foreach ($connections as $connection) {
            if (!in_array($connection->getClass(), ['Fusio.Impl.Connection.System', 'Fusio.Adapter.Sql.Connection.Sql', 'Fusio.Adapter.Sql.Connection.SqlAdvanced'])) {
                continue;
            }

            $instance = $this->connector->getConnection($connection->getName());
            if (!$instance instanceof Connection) {
                continue;
            }

            $schemaManager = $instance->createSchemaManager();
            $tableNames = $schemaManager->listTableNames();

            yield 'Tables for connection: ' . $connection->getName();

            foreach ($tableNames as $tableName) {
                yield 'Name: ' . $tableName;
                yield 'Columns:';

                $columns = $schemaManager->listTableColumns($tableName);
                foreach ($columns as $column) {
                    yield '* ' . $column->getName() . ': ' . Type::lookupName($column->getType());
                }
            }
        }
    }

    private function getFormat(): ResponseRequestText
    {
        $format = new ResponseRequestTextFormatJsonSchema();
        $format->setName('Action_Schema');
        $format->setSchema([
            'type' => 'object',
            'properties' => [
                'name' => [
                    'description' => 'Contains a short precise name which summarizes the generated PHP code with only alphanumeric characters, hyphens and underscores',
                    'type' => 'string',
                ],
                'code' => [
                    'description' => 'Contains the generated PHP code',
                    'type' => 'string',
                ]
            ],
            'required' => ['name', 'code'],
            'additionalProperties' => false,
        ]);
        $format->setStrict(true);

        $text = new ResponseRequestText();
        $text->setFormat($format);

        return $text;
    }

    private function getFirstContent(ResponseResponse $response): ?string
    {
        $outputs = $response->getOutput() ?? [];

        foreach ($outputs as $output) {
            if ($output instanceof ResponseResponseOutputMessage) {
                $contents = $output->getContent() ?? [];

                return $contents[0] ?? null;
            }
        }

        return null;
    }

    private function buildAction(string $content, ResponseResponse $response): Action
    {
        $config = Parser::decode($content);
        if (!$config instanceof stdClass) {
            throw new InternalServerErrorException('Invalid response returned');
        }

        $name = $config->name ?? null;
        $code = $config->code ?? null;

        $config = new ActionConfig();
        $config->put('code', $code);

        $metadata = new Metadata();
        $metadata->put('id', $response->getId());

        $action = new Action();
        $action->setClass(ClassName::serialize(WorkerPHPLocal::class));
        $action->setName($name);
        $action->setConfig($config);
        $action->setMetadata($metadata);
        return $action;
    }
}
