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
        $result = $this->connection->fetchAll('SELECT source FROM fusio_schema WHERE name LIKE :name', ['name' => ltrim($uri->getPath(), '/')]);

        $definitions = [];
        foreach ($result as $row) {
            if (strpos($row['source'], '{') === false) {
                // in case source is a class skip
                continue;
            }

            $data = Parser::decode($row['source']);
            if (isset($data->definitions)) {
                $definitions = array_merge($definitions, (array) $data->definitions);
            }
        }

        $return = new \stdClass();
        $return->definitions = (object) $definitions;
        return $return;
    }
}
