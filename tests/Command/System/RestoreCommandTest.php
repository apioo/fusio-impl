<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table;
use Fusio\Impl\Tests\DbTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RestoreCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RestoreCommandTest extends DbTestCase
{
    #[DataProvider('restoreProvider')]
    public function testCommandRestore(string $type, int|string $id, int $status)
    {
        $column = is_numeric($id) ? 'id' : 'name';

        // delete record
        $this->connection->update('fusio_' . $type, ['status' => 0], [$column => $id]);

        $command = Environment::getService(Application::class)->find('system:restore');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'type'    => $type,
            'id'      => $id,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/Restored 1 record/', $display, $display);

        $row = $this->connection->fetchAssociative('SELECT status FROM fusio_' . $type . ' WHERE ' . $column . ' = :id', ['id' => $id]);
        $this->assertEquals($status, $row['status']);
    }

    public static function restoreProvider(): array
    {
        return [
            ['action', 1, Table\Action::STATUS_ACTIVE],
            ['action', 'Util-Static-Response', Table\Action::STATUS_ACTIVE],
            ['app', 1, Table\App::STATUS_ACTIVE],
            ['app', 'Foo-App', Table\App::STATUS_ACTIVE],
            ['connection', 1, Table\Connection::STATUS_ACTIVE],
            ['connection', 'System', Table\Connection::STATUS_ACTIVE],
            ['cronjob', 1, Table\Cronjob::STATUS_ACTIVE],
            ['cronjob', 'Test-Cron', Table\Cronjob::STATUS_ACTIVE],
            ['operation', 1, Table\Operation::STATUS_ACTIVE],
            ['operation', 'test.listFoo', Table\Operation::STATUS_ACTIVE],
            ['schema', 1, Table\Schema::STATUS_ACTIVE],
            ['schema', 'Entry-Schema', Table\Schema::STATUS_ACTIVE],
            ['user', 1, Table\User::STATUS_ACTIVE],
            ['user', 'Deleted', Table\User::STATUS_ACTIVE],
        ];
    }

    #[DataProvider('restoreInvalidProvider')]
    public function testCommandRestoreInvalid(string $type, int $id)
    {
        $command = Environment::getService(Application::class)->find('system:restore');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'type'    => $type,
            'id'      => $id,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/Restored no record/', $display, $display);
    }

    public static function restoreInvalidProvider(): array
    {
        return [
            ['action', 1024],
            ['app', 1024],
            ['connection', 1024],
            ['cronjob', 1024],
            ['operation', 1024],
            ['schema', 1024],
            ['user', 1024],
        ];
    }
}
