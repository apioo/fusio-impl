<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service\Test;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Base;
use Fusio\Impl\Exception\Test\MissingParameterException;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Engine\DispatchInterface;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\ResponseInterface;
use PSX\Http\Stream\Stream;
use PSX\OAuth2\AccessToken;
use PSX\Schema\Exception\TraverserException;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\SchemaTraverser;
use PSX\Uri\Uri;

/**
 * Runner
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Runner
{
    private Table\Test $testTable;
    private DispatchInterface $dispatcher;
    private SchemaManagerInterface $schemaManager;
    private Service\Token $tokenService;
    private Connection $connection;
    private ConnectionTransaction $connectionTransaction;

    public function __construct(Table\Test $testTable, DispatchInterface $dispatcher, SchemaManagerInterface $schemaManager, Service\Token $tokenService, Connection $connection, ConnectionTransaction $connectionTransaction)
    {
        $this->testTable = $testTable;
        $this->dispatcher = $dispatcher;
        $this->schemaManager = $schemaManager;
        $this->tokenService = $tokenService;
        $this->connection = $connection;
        $this->connectionTransaction = $connectionTransaction;
    }

    public function authenticate(UserContext $context): AccessToken
    {
        $scopes = $this->connection->fetchFirstColumn('SELECT name FROM fusio_scope ORDER BY name ASC');
        $token = $this->tokenService->generate($context->getTenantId(), Table\Category::TYPE_DEFAULT, $context->getAppId(), $context->getUserId(), 'Fusio-Test', $scopes, '127.0.0.1', new \DateInterval('PT30M'));

        return $token;
    }

    public function run(Table\Generated\TestRow $test, Table\Generated\OperationRow $operation, AccessToken $token): void
    {
        $headers = [];
        $headers['User-Agent'] = Base::getUserAgent();

        if (!$operation->getPublic()) {
            $headers['Authorization'] = 'Bearer ' . $token->getAccessToken();
        }

        if (in_array($operation->getHttpMethod(), ['POST', 'PUT', 'PATCH'])) {
            $headers['Content-Type'] = 'application/json';
            $body = $test->getBody();
        } else {
            $body = null;
        }

        $this->connectionTransaction->beginTransaction();

        $error = null;
        $response = null;
        try {
            $response = $this->dispatch($this->buildPath($test, $operation), $operation->getHttpMethod(), $headers, $body);
        } catch (MissingParameterException $e) {
            $error = $e->getMessage();
        } catch (\Throwable $e) {
            $error = $this->getErrorMessage($e);
        } finally {
            $this->connectionTransaction->rollBack();
        }

        if ($error !== null || $response === null) {
            $this->set($test, Table\Test::STATUS_ERROR, $error, $body);
            return;
        }

        $body = (string) $response->getBody();

        if ($response->getStatusCode() !== $operation->getHttpCode()) {
            $this->set($test, Table\Test::STATUS_ERROR, 'Expected status code ' . $operation->getHttpCode() . ' got ' . $response->getStatusCode(), $body);
            return;
        }

        if (in_array($operation->getHttpMethod(), ['POST', 'PUT', 'PATCH'])) {
            if (!$this->isValidSchema($operation->getIncoming())) {
                $this->set($test, Table\Test::STATUS_WARNING, 'No incoming schema defined', $body);
                return;
            }
        }

        $outgoing = $operation->getOutgoing();
        if (!$this->isValidSchema($outgoing)) {
            $this->set($test, Table\Test::STATUS_WARNING, 'No outgoing schema defined', $body);
            return;
        }

        if ($response->getStatusCode() === 204) {
            $this->set($test, Table\Test::STATUS_SUCCESS, '', $body);
            return;
        }

        if (str_starts_with($outgoing, 'mime://')) {
            $this->set($test, Table\Test::STATUS_SUCCESS, '', $body);
            return;
        }

        try {
            $schema = $this->schemaManager->getSchema($outgoing);
            $data = \json_decode((string) $response->getBody());

            (new SchemaTraverser(ignoreUnknown: false))->traverse($data, $schema);

            $this->set($test, Table\Test::STATUS_SUCCESS, '', $body);
        } catch (TraverserException $e) {
            $this->set($test, Table\Test::STATUS_ERROR, $e->getMessage(), $body);
        } catch (\Throwable $e) {
            $this->set($test, Table\Test::STATUS_ERROR, $this->getErrorMessage($e), $body);
        }
    }

    private function dispatch(string $uri, string $method, array $headers, ?string $body): ResponseInterface
    {
        $request  = new Request(Uri::parse($uri), $method, $headers, $body);
        $response = new Response();
        $response->setBody(new Stream(fopen('php://memory', 'r+')));

        return $this->dispatcher->route($request, $response);
    }

    private function set(Table\Generated\TestRow $test, int $status, ?string $message, ?string $response): void
    {
        $test->setStatus($status);
        $test->setMessage($message);
        $test->setResponse($response);
        $this->testTable->update($test);
    }

    private function buildPath(Table\Generated\TestRow $test, Table\Generated\OperationRow $operation): string
    {
        $uriFragments = $this->getUriFragments($test);
        $queryParameters = $this->getQueryParameters($test);

        $result = [];
        $parts = explode('/', $operation->getHttpPath());
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            if (isset($part[0]) && $part[0] == ':') {
                $name = substr($part, 1);

                $result[] = $uriFragments[$name] ?? throw new MissingParameterException('Missing parameter "' . $name . '" in path');
            } elseif (isset($part[0]) && $part[0] == '$') {
                $pos = strpos($part, '<');
                if ($pos !== false) {
                    $name = substr($part, 1, $pos - 1);
                } else {
                    $name = substr($part, 1);
                }

                $result[] = $uriFragments[$name] ?? throw new MissingParameterException('Missing parameter "' . $name . '" in path');
            } elseif (isset($part[0]) && $part[0] == '*') {
                $name = substr($part, 1);

                $result[] = $uriFragments[$name] ?? throw new MissingParameterException('Missing parameter "' . $name . '" in path');
            } else {
                $result[] = $part;
            }
        }

        $path = '/' . implode('/', $result);

        if (!empty($queryParameters)) {
            return $path . '?' . http_build_query($queryParameters);
        } else {
            return $path;
        }
    }

    private function isValidSchema(?string $schema): bool
    {
        return !empty($schema) &&
            $schema !== 'schema://Passthru' &&
            $schema !== 'php+class://PSX.Api.Model.Passthru';
    }

    private function getErrorMessage(\Throwable $e): string
    {
        $message = 'Error: ' . $e->getMessage() . "\n";
        $message.= $e->getTraceAsString();

        return $message;
    }

    private function getUriFragments(Table\Generated\TestRow $test): array
    {
        $result = [];
        $uriFragments = $test->getUriFragments();
        if (!empty($uriFragments)) {
            parse_str($uriFragments, $result);
        }

        return $result;
    }

    private function getQueryParameters(Table\Generated\TestRow $test): array
    {
        $result = [];
        $parameters = $test->getParameters();
        if (!empty($parameters)) {
            parse_str($parameters, $result);
        }

        return $result;
    }
}
