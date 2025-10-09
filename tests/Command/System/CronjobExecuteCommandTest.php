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

use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * CronjobExecuteCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CronjobExecuteCommandTest extends DbTestCase
{
    public function testCommand()
    {
        $command = Environment::getService(Application::class)->find('cronjob');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertStringContainsString('Execution successful', $actual);

        $cronjob = $this->connection->fetchAssociative('SELECT * FROM fusio_cronjob WHERE name = :name', ['name' => 'Test-Cron']);

        $this->assertEquals(2, $cronjob['id']);
        $this->assertEquals(1, $cronjob['status']);
        $this->assertEquals('Test-Cron', $cronjob['name']);
        $this->assertEquals('* * * * *', $cronjob['cron']);
        $this->assertEquals('Sql-Select-All', $cronjob['action']);
        // The command only inserts jobs to the message bus so they are executed later on
        //$this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($cronjob['execute_date'])));
        $this->assertEquals(0, $cronjob['exit_code']);
    }
}
