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

use Fusio\Impl\Base;
use Fusio\Impl\Database\Installer;
use Fusio\Impl\Database\VersionInterface;
use Fusio\Impl\Tests\DbTestCase;

/**
 * InstallerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class InstallerTest extends DbTestCase
{
    /**
     * Checks whether we have an database version
     */
    public function testVersion()
    {
        $this->assertInstanceOf(VersionInterface::class, Installer::getVersion(Base::getVersion()), 'No database version class was provided');
    }

    /**
     * Checks whether this version is in the upgrade path
     */
    public function testUpgradePath()
    {
        $this->assertEquals(Base::getVersion(), current(Installer::getUpgradePath()), 'The current version must be in the upgrade path');
    }

    public function testGetLatestVersion()
    {
        $this->assertInstanceOf(VersionInterface::class, Installer::getLatestVersion());
    }

    public function testGetPathBetweenVersions()
    {
        $from   = 2;
        $to     = 0;
        $path   = Installer::getUpgradePath();
        $result = Installer::getPathBetweenVersions($path[$from], $path[$to]);

        $this->assertEquals(array_reverse(array_slice($path, $to, $from)), $result);
    }

    /**
     * Run the installation script to check whether an installation works
     * without errors
     */
    public function testInstall()
    {
        // create a copy of the current schema
        $sm       = $this->connection->getSchemaManager();
        $toSchema = $sm->createSchema();

        $this->removeAllTables();

        // execute install
        $installer = new Installer($this->connection);
        $installer->install(Base::getVersion());

        // @TODO make checks to verify that the installation works

        $this->removeAllTables();

        // restore the schema
        $fromSchema = $sm->createSchema();
        $queries    = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($queries as $sql) {
            $this->connection->executeUpdate($sql);
        }
    }

    /**
     * Executes all executeUpgrade methods of each version
     */
    public function testUpgrade()
    {
        // create a copy of the current schema
        $sm       = $this->connection->getSchemaManager();
        $toSchema = $sm->createSchema();

        $this->removeAllTables();

        // execute upgrade
        $path = array_reverse(Installer::getUpgradePath());
        $lastVersion = array_shift($path);

        $installer = new Installer($this->connection);
        $installer->install($lastVersion);

        foreach ($path as $version) {
            $installer->upgrade($lastVersion, $version);
            $lastVersion = $version;
        }

        // @TODO make checks to verify that the installation works

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
