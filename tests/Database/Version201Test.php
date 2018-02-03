<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Database;

use Fusio\Impl\Database\Installer;
use Fusio\Impl\Tests\DbTestCase;

/**
 * Version201Test
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Version201Test extends DbTestCase
{
    public function testUpgrade()
    {
        // create a copy of the current schema
        $sm       = $this->connection->getSchemaManager();
        $toSchema = $sm->createSchema();

        $this->removeAllTables();

        // execute upgrade
        $installer = new Installer($this->connection);
        $installer->install('2.0.0');
        $installer->upgrade('2.0.0', '2.0.1');

        $this->assertEquals(48, $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_routes WHERE priority >= 0x10000000 AND path LIKE ?', ['/backend%']));
        $this->assertEquals(14, $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_routes WHERE priority >= 0x8000000 AND priority < 0x10000000 AND path LIKE ?', ['/consumer%']));
        $this->assertEquals(3, $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_routes WHERE priority >= 0x4000000 AND priority < 0x8000000 AND path LIKE ?', ['/authorization%']));
        $this->assertEquals(2, $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_routes WHERE priority >= 0x2000000 AND priority < 0x4000000 AND path LIKE ?', ['/doc%']));
        $this->assertEquals(4, $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_routes WHERE priority >= 0x1000000 AND priority < 0x2000000 AND path LIKE ?', ['/export%']));
        $this->assertEquals(1, $this->connection->fetchColumn('SELECT COUNT(*) FROM fusio_routes WHERE priority < 0x1000000 OR priority IS NULL'));

        $this->removeAllTables();

        // restore the schema
        $fromSchema = $sm->createSchema();
        $queries    = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($queries as $sql) {
            $this->connection->executeUpdate($sql);
        }
    }

    protected function removeAllTables()
    {
        $sm         = $this->connection->getSchemaManager();
        $fromSchema = $sm->createSchema();
        $toSchema   = clone $fromSchema;

        foreach ($fromSchema->getTables() as $table) {
            $toSchema->dropTable($table->getName());
        }

        $queries = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($queries as $sql) {
            $this->connection->executeUpdate($sql);
        }
    }
}
