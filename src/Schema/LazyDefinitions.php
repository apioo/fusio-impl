<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Schema;

use Doctrine\DBAL\Connection;
use PSX\Schema\Definitions;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\TypeInterface;
use PSX\Schema\TypeNotFoundException;

/**
 * LazyDefinitions
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class LazyDefinitions extends Definitions
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    public function getType(string $name): TypeInterface
    {
        try {
            return parent::getType($name);
        } catch (TypeNotFoundException $e) {
        }

        $type = $this->loadType($name);
        if (!$type instanceof TypeInterface) {
            throw new TypeNotFoundException('Type "' . $name . '" not found', 'self', $name);
        }

        return $type;
    }

    public function hasType(string $name): bool
    {
        $result = parent::hasType($name);

        if ($result === true) {
            return true;
        }

        return $this->loadType($name) instanceof TypeInterface;
    }

    private function loadType($name): ?TypeInterface
    {
        if (is_numeric($name)) {
            $column = 'id';
        } else {
            $column = 'name';
        }

        $row = $this->connection->fetchAssoc('SELECT name, cache FROM fusio_schema WHERE ' . $column . ' = :name', ['name' => $name]);
        if (empty($row)) {
            return null;
        }

        $parser = new TypeSchema();
        $schema = $parser->parse($row['cache']);
        $type = $schema->getType();

        $this->addType($row['name'], $type);
        $this->merge($schema->getDefinitions());

        return $type;
    }
}
