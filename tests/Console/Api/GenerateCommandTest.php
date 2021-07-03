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

namespace Fusio\Impl\Tests\Console\Api;

use Fusio\Impl\Tests\Fixture;
use PSX\Api\GeneratorFactoryInterface;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * GenerateCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class GenerateCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('api:generate');
        $filter = 'internal';

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '-i'      => $filter,
            '-f'      => GeneratorFactoryInterface::CLIENT_PHP,
            'dir'     => __DIR__ . '/output',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertRegExp('/Successful!/', trim($actual));

        // check file
        $this->assertTrue(is_file(__DIR__ . '/output/sdk-client-php-' . $filter . '.zip'));
    }
}
