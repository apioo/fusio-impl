<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Schema\Schema;
use Symfony\Component\Console\Command\Command;
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

        $answers = ['1', 'bar', 'bar@bar.com', 'test1234!', 'test1234!'];
        $helper  = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream($answers));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--status' => '1',
            '--username' => 'bar',
            '--email' => 'bar@bar.com',
            '--password' => 'test1234!',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertContains('Created user bar successful', $actual);

        // check user
        $schema = $this->connection->fetchAssoc('SELECT id, provider, status, remoteId, name, email, password FROM fusio_user ORDER BY id DESC');

        $this->assertEquals(6, $schema['id']);
        $this->assertEquals(1, $schema['provider']);
        $this->assertEquals(1, $schema['status']);
        $this->assertEquals(null, $schema['remoteId']);
        $this->assertEquals('bar', $schema['name']);
        $this->assertEquals('bar@bar.com', $schema['email']);
        $this->assertNotEmpty($schema['password']);
    }

    protected function getInputStream(array $input)
    {
        $stream = fopen('php://memory', 'r+', false);
        foreach ($input as $line) {
            fwrite($stream, $line . "\n");
        }
        rewind($stream);

        return $stream;
    }
}
