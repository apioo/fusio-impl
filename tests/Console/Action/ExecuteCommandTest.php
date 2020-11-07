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

namespace Fusio\Impl\Tests\Console\Action;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ExecuteCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExecuteCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('action:execute');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'action'  => 'Sql-Select-All',
        ]);

        $actual = $commandTester->getDisplay();
        $expect = <<<TEXT
{
    "totalResults": 2,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27T19:59:15+00:00"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
TEXT;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testCommandParameters()
    {
        $command = Environment::getService('console')->find('action:execute');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'action'  => 'Sql-Select-All',
            '--parameters' => 'count=1',
        ]);

        $actual = $commandTester->getDisplay();
        $expect = <<<TEXT
{
    "totalResults": 2,
    "itemsPerPage": 1,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
TEXT;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testCommandBody()
    {
        $command = Environment::getService('console')->find('action:execute');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'action'  => 'Sql-Insert',
            '--method' => 'POST',
            '--body' => json_encode(['title' => 'bar', 'content' => 'bar', 'date' => date('Y-m-d H:i:s')]),
        ]);

        $actual = $commandTester->getDisplay();
        $expect = <<<TEXT
{
    "success": true,
    "message": "Entry successful created",
    "id": "3"
}
TEXT;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}
