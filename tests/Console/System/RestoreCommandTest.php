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

namespace Fusio\Impl\Tests\Console\System;

use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Table;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RestoreCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RestoreCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    /**
     * @dataProvider restoreProvider
     */
    public function testCommandRestore($type, $id, $status)
    {
        $column = is_numeric($id) ? 'id' : ($type == 'routes' ? 'path' : 'name');

        // delete record
        $this->connection->update('fusio_' . $type, ['status' => 0], [$column => $id]);

        $command = Environment::getService('console')->find('system:restore');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'type'    => $type,
            'id'      => $id,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertRegExp('/Restored 1 record/', $display, $display);

        $row = $this->connection->fetchAssoc('SELECT status FROM fusio_' . $type . ' WHERE ' . $column . ' = :id', ['id' => $id]);
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
            ['routes', 1, Table\Routes::STATUS_ACTIVE],
            ['routes', '/foo', Table\Routes::STATUS_ACTIVE],
            ['schema', 1, Table\Schema::STATUS_ACTIVE],
            ['schema', 'Entry-Schema', Table\Schema::STATUS_ACTIVE],
            ['user', 1, Table\User::STATUS_DISABLED],
            ['user', 'Deleted', Table\User::STATUS_DISABLED],
        ];
    }

    /**
     * @dataProvider restoreInvalidProvider
     */
    public function testCommandRestoreInvalid($type, $id)
    {
        $command = Environment::getService('console')->find('system:restore');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'type'    => $type,
            'id'      => $id,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertRegExp('/Restored no record/', $display, $display);
    }

    public function restoreInvalidProvider()
    {
        return [
            ['action', 1024],
            ['app', 1024],
            ['connection', 1024],
            ['cronjob', 1024],
            ['routes', 1024],
            ['schema', 1024],
            ['user', 1024],
        ];
    }
}
