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

namespace Fusio\Impl\Service\Form;

use Fusio\Impl\Table;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\SchemaManagerInterface;

/**
 * JsonSchemaResolver
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class JsonSchemaResolver
{
    public function __construct(private Table\Operation $operationTable, private SchemaManagerInterface $schemaManager)
    {
    }

    public function resolveIncomingByOperationId(int $operationId): ?array
    {
        $operation = $this->operationTable->find($operationId);
        if (!$operation instanceof Table\Generated\OperationRow) {
            return null;
        }

        return $this->resolveIncoming($operation);
    }

    public function resolveIncoming(Table\Generated\OperationRow $operation): ?array
    {
        $incoming = $operation->getIncoming();
        if (empty($incoming)) {
            return null;
        }

        return $this->buildJsonSchema($incoming);
    }

    public function resolveOutgoing(Table\Generated\OperationRow $operation): ?array
    {
        $outgoing = $operation->getOutgoing();
        if (empty($outgoing)) {
            return null;
        }

        return $this->buildJsonSchema($outgoing);
    }

    private function buildJsonSchema(?string $schema): ?array
    {
        if ($schema === null) {
            return null;
        }

        $schema = $this->schemaManager->getSchema($schema);

        return (new JsonSchema(inlineDefinitions: true))->toArray($schema->getDefinitions(), $schema->getRoot());
    }
}
