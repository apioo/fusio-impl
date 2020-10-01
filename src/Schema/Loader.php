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
use Fusio\Impl\Service;
use PSX\Schema\SchemaInterface;
use RuntimeException;

/**
 * Loader
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Loader
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getSchema($schemaId)
    {
        if (is_numeric($schemaId)) {
            $column = 'id';
        } else {
            $column = 'name';
        }

        $row = $this->connection->fetchAssoc('SELECT name, cache FROM fusio_schema WHERE ' . $column . ' = :id', array('id' => $schemaId));

        if (!empty($row)) {
            $cache = isset($row['cache']) ? $row['cache'] : null;

            if (!empty($cache)) {
                $cache = Service\Schema::unserializeCache($cache);

                if ($cache instanceof SchemaInterface) {
                    return $cache;
                }
            }

            throw new RuntimeException(sprintf('Schema %s cache not available', $row['name']));
        } else {
            throw new RuntimeException('Invalid schema reference ' . $schemaId);
        }
    }
}
