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

namespace Fusio\Impl\Tests\Console\User;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * AddCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AddCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('user:add');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--status' => '1',
            '--username' => 'bar',
            '--email' => 'bar@bar.com',
            '--password' => 'test1234!',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertStringContainsString('Created user bar successful', $actual);

        // check user
        $user = $this->connection->fetchAssoc('SELECT id, provider, status, remote_id, name, email, password FROM fusio_user ORDER BY id DESC');

        $this->assertEquals(6, $user['id']);
        $this->assertEquals(1, $user['provider']);
        $this->assertEquals(1, $user['status']);
        $this->assertEquals(null, $user['remote_id']);
        $this->assertEquals('bar', $user['name']);
        $this->assertEquals('bar@bar.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }
}
