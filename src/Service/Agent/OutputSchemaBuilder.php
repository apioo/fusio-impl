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

namespace Fusio\Impl\Service\Agent;

use Fusio\Impl\Framework\Schema\Scheme;
use Fusio\Impl\Table\Generated\OperationRow;
use PSX\Schema\Generator\JsonSchemaOpenAI;
use PSX\Schema\SchemaManager;

/**
 * OutputSchemaBuilder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class OutputSchemaBuilder
{
    public function __construct(private SchemaManager $schemaManager)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(OperationRow $operation): array
    {
        $outgoing = $operation->getOutgoing();
        if (empty($outgoing)) {
            return [];
        }

        [$scheme] = Scheme::split($outgoing);
        if ($scheme === Scheme::MIME) {
            return [];
        }

        $schema = $this->schemaManager->getSchema($outgoing);

        return new JsonSchemaOpenAI()->toArray($schema->getDefinitions(), $schema->getRoot());
    }
}
