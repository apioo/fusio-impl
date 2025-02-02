<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * UserAddCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserAddCommandTest extends DbTestCase
{
    public function testCommand()
    {
        $command = Environment::getService(Application::class)->find('adduser');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--role' => '1',
            '--username' => 'bar',
            '--email' => 'bar@bar.com',
            '--password' => 'test1234!',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertStringContainsString('Created user bar successful', $actual);

        // check user
        $user = $this->connection->fetchAssociative('SELECT role_id, identity_id, status, remote_id, name, email, password FROM fusio_user ORDER BY id DESC');

        $this->assertEquals(1, $user['role_id']);
        $this->assertEquals(null, $user['identity_id']);
        $this->assertEquals(1, $user['status']);
        $this->assertEquals(null, $user['remote_id']);
        $this->assertEquals('bar', $user['name']);
        $this->assertEquals('bar@bar.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }
}
