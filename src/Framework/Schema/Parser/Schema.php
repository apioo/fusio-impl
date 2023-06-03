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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
        $source = $this->connection->fetchOne('SELECT source FROM fusio_schema WHERE name LIKE :name', ['name' => ltrim($schema, '/')]);
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
