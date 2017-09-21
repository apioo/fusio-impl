<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\System\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Service\System\Migration\DBAL\QueryBucket;

/**
 * DBAL
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DBAL implements StrategyInterface
{
    public function execute($connection, $path)
    {
        if (!$connection instanceof Connection) {
            throw new \InvalidArgumentException('Connection type is not supported');
        }

        $toSchema = new Schema();
        $bucket   = new QueryBucket();
        $platform = $connection->getDatabasePlatform();

        self::executeMigration($toSchema, $bucket, $platform, $path);

        if (count($toSchema->getTables()) > 0) {
            // run migration
            $fromSchema = $connection->getSchemaManager()->createSchema();
            $queries    = $fromSchema->getMigrateToSql($toSchema, $platform);

            foreach ($queries as $query) {
                $connection->query($query);
            }
        }

        if ($bucket->hasQueries()) {
            $queries = $bucket->getQueries();
            foreach ($queries as $sql) {
                list($query, $params, $types) = $sql;
                $connection->executeQuery($query, $params, $types);
            }
        }
    }

    /**
     * If we include the schema we only want to have the $schema variable in the
     * scope. The required file uses the $schema and defines the needed tables
     *
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @param \Fusio\Impl\Service\System\Migration\DBAL\QueryBucket $migration
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @param string $file
     */
    private static function executeMigration(Schema $schema, QueryBucket $migration, AbstractPlatform $platform, $file)
    {
        require $file;
    }
}
