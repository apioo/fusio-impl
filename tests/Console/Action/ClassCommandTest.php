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
| HTTP-Proxy           | Fusio\Adapter\Http\Action\HttpProxy          |
| HTTP-Request         | Fusio\Adapter\Http\Action\HttpRequest        |
| SQL-Builder          | Fusio\Adapter\Sql\Action\SqlBuilder          |
| SQL-Execute          | Fusio\Adapter\Sql\Action\SqlExecute          |
| SQL-Fetch-All        | Fusio\Adapter\Sql\Action\SqlFetchAll         |
| SQL-Fetch-Row        | Fusio\Adapter\Sql\Action\SqlFetchRow         |
| SQL-Table            | Fusio\Adapter\Sql\Action\SqlTable            |
| Util-Cache           | Fusio\Adapter\Util\Action\UtilCache          |
| Util-Composite       | Fusio\Adapter\Util\Action\UtilComposite      |
| Util-Condition       | Fusio\Adapter\Util\Action\UtilCondition      |
| Util-Pipe            | Fusio\Adapter\Util\Action\UtilPipe           |
| Util-Processor       | Fusio\Adapter\Util\Action\UtilProcessor      |
| Util-Static-Response | Fusio\Adapter\Util\Action\UtilStaticResponse |
| Util-Transform       | Fusio\Adapter\Util\Action\UtilTransform      |
| Util-Try-Catch       | Fusio\Adapter\Util\Action\UtilTryCatch       |
| Util-Validator       | Fusio\Adapter\Util\Action\UtilValidator      |
+----------------------+----------------------------------------------+

TEXT;

        Assert::assertEqualsIgnoreWhitespace($expect, $actual);
    }
}
