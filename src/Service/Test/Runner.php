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

namespace Fusio\Impl\Service\Test;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Exception\Test\MissingParameterException;
use Fusio\Impl\Exception\Test\ParameterNotDescribedException;
use Fusio\Impl\Service;
use Fusio\Impl\Base;
use Fusio\Impl\Table;
use PSX\Engine\DispatchInterface;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\ResponseInterface;
use PSX\Http\Stream\Stream;
use PSX\OAuth2\AccessToken;
use PSX\Schema\Exception\ValidationException;
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
    private Service\User\Authenticator $authenticatorService;

    public function __construct(Table\Test $testTable, DispatchInterface $dispatcher, SchemaManagerInterface $schemaManager, Service\Token $tokenService, Service\User\Authenticator $authenticatorService)
    {
        $this->testTable = $testTable;
        $this->dispatcher = $dispatcher;
        $this->schemaManager = $schemaManager;
        $this->tokenService = $tokenService;
        $this->authenticatorService = $authenticatorService;
    }

    public function authenticate(UserContext $context): AccessToken
    {
        $scopes = $this->authenticatorService->getAvailableScopes($context->getTenantId(), $context->getUserId());
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

        $this->testTable->beginTransaction();

        $error = null;
        $response = null;
        try {
            $response = $this->dispatch($this->buildPath($operation->getHttpPath(), $operation->getParameters(), $test->getParameters()), $operation->getHttpMethod(), $headers, $body);
        } catch (MissingParameterException|ParameterNotDescribedException $e) {
            $error = $e->getMessage();
        } catch (\Throwable $e) {
            $error = $this->getErrorMessage($e);
        } finally {
            $this->testTable->rollBack();
        }

        if ($error !== null || $response === null) {
            $test->setStatus(Table\Test::STATUS_ERROR);
            $test->setMessage($error);
            $test->setResponse($body);
            $this->testTable->update($test);

            return;
        }

        $body = (string) $response->getBody();

        if ($response->getStatusCode() !== $operation->getHttpCode()) {
            $test->setStatus(Table\Test::STATUS_ERROR);
            $test->setMessage('Expected status code ' . $operation->getHttpCode() . ' got ' . $response->getStatusCode());
            $test->setResponse($body);
            $this->testTable->update($test);

            return;
        }

        if (in_array($operation->getHttpMethod(), ['POST', 'PUT', 'PATCH'])) {
            if (!$this->isValidSchema($operation->getIncoming())) {
                $test->setStatus(Table\Test::STATUS_WARNING);
                $test->setMessage('No incoming schema defined');
                $test->setResponse($body);
                $this->testTable->update($test);

                return;
            }
        }

        $outgoing = $operation->getOutgoing();
        if (!$this->isValidSchema($outgoing)) {
            $test->setStatus(Table\Test::STATUS_WARNING);
            $test->setMessage('No outgoing schema defined');
            $test->setResponse($body);
            $this->testTable->update($test);

            return;
        }

        if ($response->getStatusCode() === 204) {
            $test->setStatus(Table\Test::STATUS_SUCCESS);
            $test->setMessage('');
            $test->setResponse($body);
            $this->testTable->update($test);

            return;
        }

        try {
            $schema = $this->schemaManager->getSchema($outgoing);
            $data = \json_decode((string) $response->getBody());

            (new SchemaTraverser(ignoreUnknown: false))->traverse($data, $schema);

            $test->setStatus(Table\Test::STATUS_SUCCESS);
            $test->setMessage('');
            $test->setResponse($body);
            $this->testTable->update($test);
        } catch (ValidationException $e) {
            $test->setStatus(Table\Test::STATUS_ERROR);
            $test->setMessage($e->getMessage());
            $test->setResponse($body);
            $this->testTable->update($test);
        } catch (\Throwable $e) {
            $test->setStatus(Table\Test::STATUS_ERROR);
            $test->setMessage($this->getErrorMessage($e));
            $test->setResponse($body);
            $this->testTable->update($test);
        }
    }

    private function dispatch(string $uri, string $method, array $headers, ?string $body): ResponseInterface
    {
        $request  = new Request(Uri::parse($uri), $method, $headers, $body);
        $response = new Response();
        $response->setBody(new Stream(fopen('php://memory', 'r+')));

        return $this->dispatcher->route($request, $response);
    }

    private function buildPath(string $httpPath, ?string $operationSchema, ?string $testParameters): string
    {
        $parameters = [];
        if (!empty($testParameters)) {
            parse_str($testParameters, $parameters);
        }

        $names = [];
        $result = [];
        $parts = explode('/', $httpPath);
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            if (isset($part[0]) && $part[0] == ':') {
                $name = substr($part, 1);

                $names[] = $name;
                $result[] = $parameters[$name] ?? throw new MissingParameterException('Missing parameter "' . $name . '" in path');
            } elseif (isset($part[0]) && $part[0] == '$') {
                $pos = strpos($part, '<');
                if ($pos !== false) {
                    $name = substr($part, 1, $pos - 1);
                } else {
                    $name = substr($part, 1);
                }

                $names[] = $name;
                $result[] = $parameters[$name] ?? throw new MissingParameterException('Missing parameter "' . $name . '" in path');
            } elseif (isset($part[0]) && $part[0] == '*') {
                $name = substr($part, 1);

                $names[] = $name;
                $result[] = $parameters[$name] ?? throw new MissingParameterException('Missing parameter "' . $name . '" in path');
            } else {
                $result[] = $part;
            }
        }

        if (!empty($operationSchema)) {
            $schema = \json_decode($operationSchema, true);
            if (!is_array($schema)) {
                $schema = [];
            }
        } else {
            $schema = [];
        }

        foreach ($names as $name) {
            if (!isset($schema[$name])) {
                throw new ParameterNotDescribedException('Parameter "' . $name . '" is not described at operation');
            }
        }

        if (count($schema) !== count($names)) {
            throw new ParameterNotDescribedException('The operation has ' . count($names) . ' dynamic parameters, only ' . count($schema) . ' parameters are described at the operation');
        }

        return '/' . implode('/', $result);
    }

    private function isValidSchema(?string $schema): bool
    {
        return !empty($schema) && $schema !== 'schema://Passthru' && $schema !== 'php+class://PSX.Api.Model.Passthru';
    }

    private function getErrorMessage(\Throwable $e): string
    {
        $message = 'Error: ' . $e->getMessage() . "\n";
        $message.= $e->getTraceAsString();

        return $message;
    }
}
