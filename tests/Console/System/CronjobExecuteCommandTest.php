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

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * CronjobExecuteCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CronjobExecuteCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('cronjob');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'cronjob' => 'Test-Cron',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertStringContainsString('Execution successful', $actual);

        $cronjob = $this->connection->fetchAssoc('SELECT * FROM fusio_cronjob WHERE name = :name', ['name' => 'Test-Cron']);

        $this->assertEquals(5, $cronjob['id']);
        $this->assertEquals(1, $cronjob['status']);
        $this->assertEquals('Test-Cron', $cronjob['name']);
        $this->assertEquals('*/30 * * * *', $cronjob['cron']);
        $this->assertEquals('Sql-Select-All', $cronjob['action']);
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($cronjob['execute_date'])));
        $this->assertEquals(0, $cronjob['exit_code']);
    }
}
