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
use PSX\Framework\Schema\Passthru;
use PSX\Schema\SchemaInterface;
use PSX\Schema\SchemaManager;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\SchemaManagerInterface;

/**
 * Loader
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Loader
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var SchemaManagerInterface
     */
    private $schemaManager;

    public function __construct(Connection $connection, SchemaManagerInterface $schemaManager)
    {
        $this->connection = $connection;
        $this->schemaManager = $schemaManager;
    }

    public function getSchema($schemaId): SchemaInterface
    {
        $source = $this->getSource($schemaId);
        return $this->schemaManager->getSchema($source);
    }

    private function getSource($schemaId): string
    {
        if (is_numeric($schemaId)) {
            $column = 'id';
        } else {
            $column = 'name';
        }

        $row = $this->connection->fetchAssoc('SELECT name, source FROM fusio_schema WHERE ' . $column . ' = :id', array('id' => $schemaId));
        $source = $row['source'] ?? null;

        if (strpos($source, '{') !== false) {
            // in case the source is a schema write it to a file
            $hash = md5($source);
            $schemaFile = PSX_PATH_CACHE . '/schema-' . $row['name'] . '-' . $hash . '.json';
            if (!is_file($schemaFile) || md5_file($schemaFile) !== $hash) {
                file_put_contents($schemaFile, $source);
            }

            $source = $schemaFile;
        }

        return $source;
    }
}
