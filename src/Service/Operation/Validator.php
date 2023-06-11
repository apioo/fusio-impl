<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Operation;

use Fusio\Impl\Table;
use Fusio\Model\Backend\Operation;
use Fusio\Model\Backend\OperationParameters;
use Fusio\Model\Backend\OperationThrows;
use PSX\Api\OperationInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Type;
use PSX\Sql\Condition;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Operation $operationTable;
    private Table\Action $actionTable;
    private Table\Schema $schemaTable;

    public function __construct(Table\Operation $operationTable, Table\Action $actionTable, Table\Schema $schemaTable)
    {
        $this->operationTable = $operationTable;
        $this->actionTable = $actionTable;
        $this->schemaTable = $schemaTable;
    }

    public function assert(Operation $operation, ?Table\Generated\OperationRow $existing = null): void
    {
        $name = $operation->getName();
        if ($name !== null) {
            $this->assertName($name, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Operation name must not be empty');
        }

        $stability = $operation->getStability();
        if ($stability !== null) {
            $this->assertStability($stability);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Stability must not be empty');
        }

        $httpPath = $operation->getHttpPath();
        if ($httpPath !== null) {
            $this->assertHttpPath($httpPath);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('HTTP path must not be empty');
        }

        $httpMethod = $operation->getHttpMethod();
        if ($httpMethod !== null) {
            $this->assertHttpMethod($httpMethod);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('HTTP method must not be empty');
        }

        $httpCode = $operation->getHttpCode();
        if ($httpCode !== null) {
            $this->assertHttpCode($httpCode, 200, 299, 'HTTP code');
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('HTTP code must not be empty');
        }

        $this->assertHttpMethodAndPathExisting($operation, $existing);
        $this->assertParameters($operation->getParameters());
        $this->assertIncoming($operation->getIncoming());

        $outgoing = $operation->getOutgoing();
        if ($outgoing !== null) {
            $this->assertOutgoing($outgoing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Outgoing schema must not be empty');
        }

        $this->assertThrows($operation->getThrows());

        $action = $operation->getAction();
        if ($action !== null) {
            $this->assertAction($operation->getAction());
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Action must not be empty');
        }
    }

    private function assertName(string $name, ?Table\Generated\OperationRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid operation name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->operationTable->findOneByName($name)) {
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

    private function assertHttpMethodAndPathExisting(Operation $operation, ?Table\Generated\OperationRow $existing): void
    {
        if ($existing instanceof Table\Generated\OperationRow && $existing->getHttpMethod() === $operation->getHttpMethod() && $existing->getHttpPath() === $operation->getHttpPath()) {
            // in case we update an existing operation and the method and path has not changed, we dont need to validate
            return;
        }

        $condition = Condition::withAnd();
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

    private function assertIncoming(?string $incoming): void
    {
        if ($incoming === null) {
            return;
        }

        $schema = $this->schemaTable->findOneByName($incoming);
        if (!$schema instanceof Table\Generated\SchemaRow) {
            throw new StatusCode\BadRequestException('Incoming schema "' . $incoming . '" does not exist');
        }
    }

    private function assertOutgoing(string $outgoing): void
    {
        $schema = $this->schemaTable->findOneByName($outgoing);
        if (!$schema instanceof Table\Generated\SchemaRow) {
            throw new StatusCode\BadRequestException('Outgoing schema "' . $outgoing . '" does not exist');
        }
    }

    private function assertThrows(?OperationThrows $throws): void
    {
        if ($throws === null) {
            return;
        }

        foreach ($throws as $statusCode => $throwName) {
            $this->assertHttpCode($statusCode, 400, 599, 'Throw');

            $schema = $this->schemaTable->findOneByName($throwName);
            if (!$schema instanceof Table\Generated\SchemaRow) {
                throw new StatusCode\BadRequestException('Throw "' . $statusCode . '" contains a schema "' . $throwName . '" which does not exist');
            }
        }
    }

    private function assertAction(string $actionName): void
    {
        $action = $this->actionTable->findOneByName($actionName);
        if (!$action instanceof Table\Generated\ActionRow) {
            throw new StatusCode\BadRequestException('Action "' . $actionName . '" does not exist');
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
