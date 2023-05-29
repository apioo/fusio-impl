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

namespace Fusio\Impl\Tests\Console\System;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Command\System\LogRotateCommand;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * LogRotateCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class LogRotateCommandTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testCommandLogRotate()
    {
        /** @var LogRotateCommand $command */
        $command = Environment::getService(Application::class)->find('system:log_rotate');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(0, $commandTester->getStatusCode());

        $schemaManager = $this->connection->createSchemaManager();
        $schema = $schemaManager->introspectSchema();

        $this->assertAuditTable($display, $schemaManager, $schema);
        $this->assertLogTable($display, $schemaManager, $schema);
    }

    private function assertAuditTable(string $display, AbstractSchemaManager $schemaManager, Schema $schema)
    {
        $this->assertRegExp('/Created audit archive table fusio_audit_/', $display, $display);
        $this->assertRegExp('/Copied 1 entries to audit archive table/', $display, $display);
        $this->assertRegExp('/Truncated audit table/', $display, $display);

        preg_match('/fusio_audit_(\d+)/', $display, $matches);
        $tableName = $matches[0];

        $this->assertTrue($schema->hasTable($tableName));

        $row = $this->connection->fetchAssociative('SELECT COUNT(*) AS cnt FROM ' . $tableName);
        $this->assertEquals(1, $row['cnt']);

        $schemaManager->dropTable($tableName);
    }
    
    private function assertLogTable(string $display, AbstractSchemaManager $schemaManager, Schema $schema)
    {
        $this->assertRegExp('/Created log archive table fusio_log_/', $display, $display);
        $this->assertRegExp('/Copied 2 entries to log archive table/', $display, $display);
        $this->assertRegExp('/Truncated log table/', $display, $display);

        preg_match('/fusio_log_(\d+)/', $display, $matches);
        $tableName = $matches[0];

        $this->assertTrue($schema->hasTable($tableName));

        $row = $this->connection->fetchAssociative('SELECT COUNT(*) AS cnt FROM ' . $tableName);
        $this->assertEquals(2, $row['cnt']);

        $row = $this->connection->fetchAssociative('SELECT COUNT(*) AS cnt FROM fusio_log');
        $this->assertEquals(0, $row['cnt']);

        $schemaManager->dropTable($tableName);
    }
}
