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

namespace Fusio\Impl\Service\Operation;

use Fusio\Engine\Exception\ActionNotFoundException;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Engine\ProcessorInterface;
use Fusio\Impl\Action\Scheme as ActionScheme;
use Fusio\Impl\Framework\Schema\Scheme as SchemaScheme;
use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Operation;
use Fusio\Model\Backend\OperationParameters;
use Fusio\Model\Backend\OperationThrows;
use PSX\Api\OperationInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Exception\InvalidSchemaException;
use PSX\Schema\Exception\ParserException;
use PSX\Schema\SchemaManagerInterface;
use PSX\Schema\Type;
use PSX\Sql\Condition;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Validator
{
    public function __construct(
        private Table\Operation $operationTable,
        private Table\Schema $schemaTable,
        private Table\Action $actionTable,
        private SchemaManagerInterface $schemaManager,
        private ProcessorInterface $processor,
        private UsageLimiter $usageLimiter
    ) {
    }

    public function assert(Operation $operation, int $categoryId, ?string $tenantId, ?Table\Generated\OperationRow $existing = null): void
    {
        $this->usageLimiter->assertOperationCount($tenantId);

        $name = $operation->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Operation name must not be empty');
            }
        }

        $stability = $operation->getStability();
        if ($stability !== null) {
            $this->assertStability($stability);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Stability must not be empty');
            }
        }

        $httpPath = $operation->getHttpPath();
        if ($httpPath !== null) {
            $this->assertHttpPath($httpPath);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('HTTP path must not be empty');
            }
        }

        $httpMethod = $operation->getHttpMethod();
        if ($httpMethod !== null) {
            $this->assertHttpMethod($httpMethod);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('HTTP method must not be empty');
            }
        }

        $httpCode = $operation->getHttpCode();
        if ($httpCode !== null) {
            $this->assertHttpCode($httpCode, 200, 299, 'HTTP code');
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('HTTP code must not be empty');
            }
        }

        $this->assertHttpMethodAndPathExisting($operation, $tenantId, $existing);
        $this->assertParameters($operation->getParameters());
        $this->assertIncoming($operation->getIncoming(), $categoryId, $tenantId);

        $outgoing = $operation->getOutgoing();
        if ($outgoing !== null) {
            $this->assertOutgoing($outgoing, $categoryId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Outgoing schema must not be empty');
            }
        }

        $this->assertThrows($operation->getThrows(), $categoryId, $tenantId);

        $action = $operation->getAction();
        if ($action !== null) {
            $this->assertAction($operation->getAction(), $categoryId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Action must not be empty');
            }
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\OperationRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid operation name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->operationTable->findOneByTenantAndName($tenantId, null, $name)) {
            throw new StatusCode\BadRequestException('Operation already exists');
        }
    }

    private function assertStability(int $stability): void
    {
        $allowedStability = [OperationInterface::STABILITY_DEPRECATED, OperationInterface::STABILITY_EXPERIMENTAL, OperationInterface::STABILITY_STABLE, OperationInterface::STABILITY_LEGACY];
        if (!in_array($stability, $allowedStability, true)) {
            throw new StatusCode\BadRequestException('Stability contain an invalid value must be one of: ' . implode(', ', $allowedStability));
        }
    }

    private function assertHttpPath(string $path): void
    {
        if (!str_starts_with($path, '/')) {
            throw new StatusCode\BadRequestException('HTTP path must start with a /');
        }

        $parts = explode('/', $path);
        array_shift($parts); // the first part is always empty

        // it is possible to use the root path /
        if (count($parts) === 1 && $parts[0] === '') {
            return;
        }

        // check reserved segments
        if (in_array(strtolower($parts[0]), $this->getReserved())) {
            throw new StatusCode\BadRequestException('HTTP path uses a path segment which is reserved for the system');
        }

        foreach ($parts as $part) {
            if (empty($part)) {
                throw new StatusCode\BadRequestException('HTTP path has an empty path segment');
            }

            if (!preg_match('/^[!-~]+$/', $part)) {
                throw new StatusCode\BadRequestException('HTTP path contains invalid characters inside a path segment');
            }
        }
    }

    private function assertHttpMethod(string $method): void
    {
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        if (!in_array($method, $allowedMethods, true)) {
            throw new StatusCode\BadRequestException('HTTP method must not be one of: ' . implode(', ', $allowedMethods));
        }
    }

    private function assertHttpCode(int $code, int $start, int $end, string $type): void
    {
        $isValid = $code >= $start && $code <= $end;
        if (!$isValid) {
            throw new StatusCode\BadRequestException($type . ' contains an HTTP status code "' . $code . '" which is not in the range between ' . $start . ' and ' . $end);
        }
    }

    private function assertHttpMethodAndPathExisting(Operation $operation, ?string $tenantId, ?Table\Generated\OperationRow $existing): void
    {
        if ($existing instanceof Table\Generated\OperationRow && $existing->getHttpMethod() === $operation->getHttpMethod() && $existing->getHttpPath() === $operation->getHttpPath()) {
            // in case we update an existing operation and the method and path has not changed, we dont need to validate
            return;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Table\Generated\OperationTable::COLUMN_HTTP_METHOD, $operation->getHttpMethod());
        $condition->equals(Table\Generated\OperationTable::COLUMN_HTTP_PATH, $operation->getHttpPath());
        if ($this->operationTable->getCount($condition) > 0) {
            throw new StatusCode\BadRequestException('An operation exists already with the same HTTP method and path');
        }
    }

    private function assertParameters(?OperationParameters $parameters): void
    {
        if ($parameters === null) {
            return;
        }

        foreach ($parameters as $name => $schema) {
            $this->assertParameterName($name);

            $scalarTypes = [Type::STRING->value, Type::BOOLEAN->value, Type::INTEGER->value, Type::NUMBER->value];
            $typeName = $schema->getType() ?? throw new StatusCode\BadRequestException('Parameter schema for "' . $name . '" must not be empty');
            if (!in_array($typeName, $scalarTypes)) {
                throw new StatusCode\BadRequestException('Parameter "' . $name . '" contains an invalid schema "' . $typeName . '" must be one of: ' . implode(', ', $scalarTypes));
            }
        }
    }

    private function assertParameterName(?string $name): void
    {
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Parameter contains an empty parameter name');
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new StatusCode\BadRequestException('Parameter name "' . $name . '" contains an invalid character, allowed are only alphanumeric characters and underscore');
        }
    }

    private function assertIncoming(?string $incoming, int $categoryId, ?string $tenantId): void
    {
        if ($incoming === null) {
            return;
        }

        $this->assertSchema($incoming, 'incoming', $categoryId, $tenantId);
    }

    private function assertOutgoing(string $outgoing, int $categoryId, ?string $tenantId): void
    {
        $this->assertSchema($outgoing, 'outgoing', $categoryId, $tenantId);
    }

    private function assertThrows(?OperationThrows $throws, int $categoryId, ?string $tenantId): void
    {
        if ($throws === null) {
            return;
        }

        foreach ($throws as $statusCode => $throwName) {
            $code = (int) $statusCode;
            if ($code !== 999) { // 999 is a special wildcard code witch represents any error
                $this->assertHttpCode($code, 400, 599, 'Throw');
            }

            $this->assertSchema($throwName, 'throw ' . $statusCode, $categoryId, $tenantId);
        }
    }

    private function assertAction(string $action, int $categoryId, ?string $tenantId): void
    {
        $scheme = ActionScheme::wrap($action);

        if (str_starts_with($scheme, 'action://')) {
            $row = $this->actionTable->findOneByTenantAndName($tenantId, $categoryId, $action);
            if (!$row instanceof Table\Generated\ActionRow) {
                throw new StatusCode\BadRequestException('Action "' . $action . '" does not exist, you need to provide an existing action name as value');
            }
        }

        try {
            $this->processor->getAction($scheme);
        } catch (ActionNotFoundException|FactoryResolveException $e) {
            throw new StatusCode\BadRequestException('Action "' . $action . '" does not exist', $e);
        }
    }

    private function assertSchema(string $schema, string $type, int $categoryId, ?string $tenantId): void
    {
        if (str_starts_with($schema, 'mime://')) {
            return;
        }

        $scheme = SchemaScheme::wrap($schema);

        if (str_starts_with($scheme, 'schema://')) {
            $row = $this->schemaTable->findOneByTenantAndName($tenantId, $categoryId, $schema);
            if (!$row instanceof Table\Generated\SchemaRow) {
                throw new StatusCode\BadRequestException(ucfirst($type) . ' schema "' . $schema . '" does not exist, you need to provide an existing schema name as value');
            }
        }

        try {
            $this->schemaManager->getSchema($scheme);
        } catch (InvalidSchemaException|ParserException $e) {
            throw new StatusCode\BadRequestException(ucfirst($type) . ' schema "' . $schema . '" does not exist', $e);
        }
    }

    private function getReserved(): array
    {
        return [
            'backend',
            'consumer',
            'system',
            'authorization',
        ];
    }
}
