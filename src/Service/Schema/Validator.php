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

namespace Fusio\Impl\Service\Schema;

use Fusio\Impl\Table;
use Fusio\Model\Backend\Schema;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Schema $schemaTable;

    public function __construct(Table\Schema $schemaTable)
    {
        $this->schemaTable = $schemaTable;
    }

    public function assert(Schema $schema, ?Table\Generated\SchemaRow $existing = null): void
    {
        $name = $schema->getName();
        if ($name !== null) {
            $this->assertName($name, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Schema name must not be empty');
        }
    }

    private function assertName(string $name, ?Table\Generated\SchemaRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid schema name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->schemaTable->findOneByName($name)) {
            throw new StatusCode\BadRequestException('Schema already exists');
        }
    }
}
