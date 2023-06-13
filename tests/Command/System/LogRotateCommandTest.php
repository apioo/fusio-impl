<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Command\System;

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
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
        $this->assertMatchesRegularExpression('/Created audit archive table fusio_audit_/', $display, $display);
        $this->assertMatchesRegularExpression('/Copied 1 entries to audit archive table/', $display, $display);
        $this->assertMatchesRegularExpression('/Truncated audit table/', $display, $display);

        preg_match('/fusio_audit_(\d+)/', $display, $matches);
        $tableName = $matches[0];

        $this->assertTrue($schema->hasTable($tableName));

        $row = $this->connection->fetchAssociative('SELECT COUNT(*) AS cnt FROM ' . $tableName);
        $this->assertEquals(1, $row['cnt']);

        $schemaManager->dropTable($tableName);
    }
    
    private function assertLogTable(string $display, AbstractSchemaManager $schemaManager, Schema $schema)
    {
        $this->assertMatchesRegularExpression('/Created log archive table fusio_log_/', $display, $display);
        $this->assertMatchesRegularExpression('/Copied 2 entries to log archive table/', $display, $display);
        $this->assertMatchesRegularExpression('/Truncated log table/', $display, $display);

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
