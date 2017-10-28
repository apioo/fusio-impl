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

namespace Fusio\Impl\Tests\Console\Schema;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Schema\Schema;
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
        $command = Environment::getService('console')->find('schema:add');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name'    => 'bar',
            'file'    => __DIR__ . '/schema.json',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Schema successful created', trim($actual));

        // check schema
        $schema = $this->connection->fetchAssoc('SELECT id, status, name, source, cache FROM fusio_schema ORDER BY id DESC');

        $this->assertEquals(4, $schema['id']);
        $this->assertEquals(1, $schema['status']);
        $this->assertEquals('bar', $schema['name']);
        $this->assertJsonStringEqualsJsonString(file_get_contents(__DIR__ . '/schema.json'), $schema['source']);
        $this->assertInstanceOf(Schema::class, unserialize($schema['cache']));
    }
}
