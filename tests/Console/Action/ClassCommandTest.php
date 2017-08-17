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

namespace Fusio\Impl\Tests\Console\Action;

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ClassCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ClassCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('action:class');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $actual = $commandTester->getDisplay();
        $expect = <<<TEXT
+----------------------+----------------------------------------------+
| Name                 | Class                                        |
+----------------------+----------------------------------------------+
| HTTP-Processor       | Fusio\Adapter\Http\Action\HttpProcessor      |
| PHP-Processor        | Fusio\Adapter\Php\Action\PhpProcessor        |
| SQL-Table            | Fusio\Adapter\Sql\Action\SqlTable            |
| Util-Static-Response | Fusio\Adapter\Util\Action\UtilStaticResponse |
| V8-Processor         | Fusio\Adapter\V8\Action\V8Processor          |
+----------------------+----------------------------------------------+

TEXT;

        $expect = str_replace(["\r\n", "\n", "\r"], "\n", $expect);
        $actual = str_replace(["\r\n", "\n", "\r"], "\n", $actual);

        $this->assertEquals($expect, $actual, $actual);
    }
}
