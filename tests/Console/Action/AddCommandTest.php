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

namespace Fusio\Impl\Tests\Console\Action;

use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Engine\Factory\Resolver\PhpClass;
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
        $command = Environment::getService('console')->find('action:add');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name'    => 'foobar',
            'class'   => UtilStaticResponse::class,
            'engine'  => PhpClass::class,
            'config'  => 'response={"foo":"bar"}',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Action successful created', trim($actual));

        // check action
        $action = $this->connection->fetchAssoc('SELECT id, status, name, class, engine, config FROM fusio_action ORDER BY id DESC');

        $this->assertEquals(5, $action['id']);
        $this->assertEquals(1, $action['status']);
        $this->assertEquals('foobar', $action['name']);
        $this->assertEquals(UtilStaticResponse::class, $action['class']);
        $this->assertEquals(PhpClass::class, $action['engine']);
        $this->assertEquals('{"response":"{\"foo\":\"bar\"}"}', $action['config']);
    }
}
