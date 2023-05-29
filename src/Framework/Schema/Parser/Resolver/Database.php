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

namespace Fusio\Impl\Framework\Schema\Parser\Resolver;

use Doctrine\DBAL\Connection;
use PSX\Json\Parser;
use PSX\Schema\Generator\TypeSchema;
use PSX\Schema\Parser\Popo;
use PSX\Schema\Parser\TypeSchema\ResolverInterface;
use PSX\Uri\Uri;

/**
 * Database
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Database implements ResolverInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function resolve(Uri $uri, ?string $basePath = null): \stdClass
    {
        $row = $this->connection->fetchAssociative('SELECT source FROM fusio_schema WHERE name LIKE :name', ['name' => ltrim($uri->getPath(), '/')]);

        if (!str_contains($row['source'], '{') && class_exists($row['source'])) {
            return $this->parseClass($row['source']);
        }

        $definitions = [];
        $data = Parser::decode($row['source']);
        if (isset($data->definitions)) {
            $definitions = array_merge($definitions, (array) $data->definitions);
        } elseif (isset($data->{'$class'}) && class_exists($data->{'$class'})) {
            return $this->parseClass($data->{'$class'});
        }

        $return = new \stdClass();
        $return->definitions = (object) $definitions;
        return $return;
    }

    private function parseClass(string $class) : \stdClass
    {
        $schema = (new Popo())->parse($class);
        $json = (new TypeSchema())->generate($schema);
        return \json_decode($json);
    }
}
