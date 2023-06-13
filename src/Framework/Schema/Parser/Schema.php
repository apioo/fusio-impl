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

namespace Fusio\Impl\Framework\Schema\Parser;

use Doctrine\DBAL\Connection;
use PSX\Schema\Exception\ParserException;
use PSX\Schema\Parser\ContextInterface;
use PSX\Schema\Parser\Popo;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\ParserInterface;
use PSX\Schema\SchemaInterface;
use PSX\Schema\SchemaManagerInterface;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Schema implements ParserInterface
{
    private Connection $connection;
    private Popo $popo;
    private TypeSchema $typeSchema;

    public function __construct(Connection $connection, SchemaManagerInterface $schemaManager)
    {
        $this->connection = $connection;
        $this->popo = new Popo();
        $this->typeSchema = new TypeSchema($schemaManager);
    }

    public function parse(string $schema, ?ContextInterface $context = null): SchemaInterface
    {
        if (is_numeric($schema)) {
            $column = 'id';
            $value  = (int) $schema;
        } else {
            $column = 'name';
            $value  = ltrim($schema, '/');
        }

        $source = $this->connection->fetchOne('SELECT source FROM fusio_schema WHERE ' . $column . ' = :value', ['value' => $value]);
        if (empty($source)) {
            throw new ParserException('Could not find schema ' . $schema);
        }

        if (!str_contains($source, '{') && class_exists($source)) {
            return $this->popo->parse($source);
        } else {
            return $this->typeSchema->parse($source, $context);
        }
    }
}
