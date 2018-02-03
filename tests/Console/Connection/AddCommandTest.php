<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Console\Connection;

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
        $command = Environment::getService('console')->find('connection:add');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name'    => 'foobar',
            'class'   => 'Fusio\Adapter\Sql\Connection\SqlAdvanced',
            'config'  => 'url=sqlite:///:memory:',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Connection successful created', trim($actual));

        // check connection
        $connection = $this->connection->fetchAssoc('SELECT id, name, class, config FROM fusio_connection ORDER BY id DESC');

        $this->assertEquals(2, $connection['id']);
        $this->assertEquals('foobar', $connection['name']);
        $this->assertEquals('Fusio\Adapter\Sql\Connection\SqlAdvanced', $connection['class']);
        $this->assertNotEmpty($connection['config']);
    }
}
