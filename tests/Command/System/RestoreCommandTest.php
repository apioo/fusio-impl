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

namespace Fusio\Impl\Tests\Command\System;

use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RestoreCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class RestoreCommandTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    /**
     * @dataProvider restoreProvider
     */
    public function testCommandRestore($type, $id, $status)
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

    public function restoreProvider()
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

    /**
     * @dataProvider restoreInvalidProvider
     */
    public function testCommandRestoreInvalid($type, $id)
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

    public function restoreInvalidProvider(): array
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
