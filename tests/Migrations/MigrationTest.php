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

namespace Fusio\Impl\Tests\Migrations;

use Fusio\Impl\Backend;
use Fusio\Impl\Console\Migration\ExecuteCommand;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * This test runs all migrations up and down
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class MigrationTest extends DbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testMigrate()
    {
        $versions = $this->getVersions();

        // migrate all versions down
        $versions = array_reverse($versions);
        $command  = $this->newExecuteCommand();

        foreach ($versions as $version) {
            $commandTester = new CommandTester($command);
            $commandTester->execute([
                'version' => $version,
                '--down'  => null,
            ], [
                'interactive' => false
            ]);
        }

        // now we should have only the migration table
        $tables = $this->connection->getSchemaManager()->listTableNames();

        $this->assertEquals(['fusio_migration_versions'], $tables);

        // migrate all versions up
        $versions = array_reverse($versions);
        $command  = $this->newExecuteCommand();

        foreach ($versions as $version) {
            $commandTester = new CommandTester($command);
            $commandTester->execute([
                'version' => $version,
                '--up'    => null,
            ], [
                'interactive' => false
            ]);
        }

        // now we should have basically the tables from the beginning
        $tables = $this->connection->getSchemaManager()->listTableNames();
        $expect = array_merge(['fusio_migration_versions', 'app_news'], array_keys(NewInstallation::getData()));

        sort($expect);
        sort($tables);

        $this->assertEquals($expect, $tables);
    }

    private function newExecuteCommand()
    {
        $command = new ExecuteCommand(
            $this->connection,
            Environment::getService('connector')
        );

        $command->setHelperSet(new HelperSet([]));

        return $command;
    }

    private function getVersions()
    {
        $result = [];
        $path   = __DIR__ . '/../../src/Migrations/Version';
        $files  = scandir($path);

        foreach ($files as $file) {
            if ($file[0] != '.') {
                $result[] = substr(pathinfo($file, PATHINFO_FILENAME), 7);
            }
        }

        return $result;
    }
}
